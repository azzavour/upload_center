<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use App\Services\ExcelFormatService;
use App\Services\MappingService;
use App\Jobs\ProcessSelloutImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Settings;

class UploadController extends Controller
{
    protected $uploadService;
    protected $formatService;
    protected $mappingService;

    public function __construct(
        UploadService $uploadService,
        ExcelFormatService $formatService,
        MappingService $mappingService
    ) {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
        $this->formatService = $formatService;
        $this->mappingService = $mappingService;
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // ✅ Validasi user memiliki department
        if (!$user->hasDepartment() && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di department manapun. Hubungi administrator.');
        }
        
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $formats = $this->formatService->getAllFormats($departmentId);
        
        return view('upload.index', compact('formats'));
    }

    public function checkFormat(Request $request)
    {
        $file = $request->file('file');
        
        if (!$file) {
            return response()->json(['error' => true, 'message' => 'File tidak ditemukan'], 400);
        }

        // Long-running preview for large files needs higher memory
        @ini_set('memory_limit', '2048M');
        
        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = ['xlsx', 'xls', 'csv'];
        
        if (!in_array($extension, $validExtensions)) {
            return response()->json([
                'error' => true, 
                'message' => 'File harus berformat XLSX, XLS, atau CSV. Extension yang terdeteksi: ' . $extension
            ], 400);
        }

        if ($file->getSize() > 40 * 1024 * 1024) {
            return response()->json(['error' => true, 'message' => 'Ukuran file maksimal 40MB'], 400);
        }

        if (!$request->format_id) {
            return response()->json(['error' => true, 'message' => 'Format ID tidak ditemukan'], 400);
        }

        try {
            // Hindari timeout saat baca file besar
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // ✅ Validasi department
            if (!$user->hasDepartment() && !$user->isAdmin()) {
                return response()->json(['error' => true, 'message' => 'Anda belum terdaftar di department manapun'], 403);
            }
            
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses department
            if (!$user->isAdmin() && $format->department_id !== $user->department_id) {
                return response()->json(['error' => true, 'message' => 'Unauthorized access to this format'], 403);
            }

            [$excelHeaders, $sampleData] = $this->extractPreviewRows($file);

            if (empty($excelHeaders)) {
                return response()->json(['error' => true, 'message' => 'File Excel kosong atau tidak valid'], 400);
            }

            $departmentId = $user->isAdmin() ? $format->department_id : $user->department_id;
            
            Log::info('Checking format for department', [
                'format_id' => $format->id,
                'department_id' => $departmentId,
                'excel_headers' => $excelHeaders
            ]);
            
            $existingMapping = $this->mappingService->findMappingByExcelColumns($format->id, $excelHeaders, $departmentId);

            $headerAnalysis = [];
            $mappingToUse = $existingMapping ? $existingMapping->column_mapping : [];

            if (empty($mappingToUse) && $this->formatService->isStandardFormat($excelHeaders, $format)) {
                foreach ($format->expected_columns as $col) {
                    $mappingToUse[$col] = $col;
                }
            }
            
            foreach ($excelHeaders as $index => $header) {
                $isMapped = false;
                $mappedTo = null;
                
                // Normalize header untuk comparison
                $normalizedHeader = strtolower(trim($header));
                $normalizedHeader = preg_replace('/\s+/', '_', $normalizedHeader);
                $normalizedHeader = preg_replace('/[^a-z0-9_]/', '', $normalizedHeader);
                
                // Cek apakah header ada di mapping
                foreach ($mappingToUse as $excelCol => $dbCol) {
                    $normalizedExcelCol = strtolower(trim($excelCol));
                    $normalizedExcelCol = preg_replace('/\s+/', '_', $normalizedExcelCol);
                    $normalizedExcelCol = preg_replace('/[^a-z0-9_]/', '', $normalizedExcelCol);
                    
                    if ($normalizedExcelCol === $normalizedHeader) {
                        $isMapped = true;
                        $mappedTo = $dbCol;
                        break;
                    }
                }

                if ($isMapped) {
                    $headerAnalysis[] = [
                        'name' => $header,
                        'status' => 'mapped',
                        'mapped_to' => $mappedTo
                    ];
                } else {
                    $headerAnalysis[] = [
                        'name' => $header,
                        'status' => 'ignored',
                        'mapped_to' => null
                    ];
                }
            }
            
            $previewPayload = [
                'headers' => $headerAnalysis,
                'data' => $sampleData
            ];

            if ($existingMapping) {
                return response()->json([
                    'is_new_format' => false,
                    'has_mapping' => true,
                    'mapping_id' => $existingMapping->id,
                    'can_proceed' => true,
                    'message' => 'Format file valid dan mapping "' . $existingMapping->mapping_name . '" ditemukan.',
                    'preview' => $previewPayload
                ], 200);
            }

            if ($this->formatService->isStandardFormat($excelHeaders, $format)) {
                return response()->json([
                    'is_new_format' => false,
                    'has_mapping' => false,
                    'can_proceed' => true,
                    'message' => 'Format standar terdeteksi. Silakan periksa pratinjau sebelum melanjutkan.',
                    'preview' => $previewPayload
                ], 200);
            }

            session(['excel_columns' => $excelHeaders]);

            return response()->json([
                'is_new_format' => true,
                'message' => 'Format baru terdeteksi. Anda akan diarahkan untuk membuat mapping.',
                'redirect' => route('mapping.create', ['format_id' => $format->id])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Upload check error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        try {
            $file = $request->file('file');
            
            if (!$file) {
                return response()->json(['error' => true, 'message' => 'File tidak ditemukan'], 400);
            }

            // Pastikan proses upload tidak cepat kehabisan memori untuk file besar
            @ini_set('memory_limit', '2048M');

            $extension = strtolower($file->getClientOriginalExtension());
            $validExtensions = ['xlsx', 'xls', 'csv'];
            
            if (!in_array($extension, $validExtensions)) {
                return response()->json(['error' => true, 'message' => 'File harus berformat XLSX, XLS, atau CSV'], 400);
            }

            if ($file->getSize() > 40 * 1024 * 1024) {
                return response()->json(['error' => true, 'message' => 'Ukuran file maksimal 40MB'], 400);
            }

            if (!$request->format_id) {
                return response()->json(['error' => true, 'message' => 'Format Excel wajib dipilih'], 400);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Validasi department
            if (!$user->hasDepartment() && !$user->isAdmin()) {
                return response()->json(['error' => true, 'message' => 'Anda belum terdaftar di department manapun. Hubungi administrator.'], 403);
            }
            
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses
            if (!$user->isAdmin() && $format->department_id !== $user->department_id) {
                return response()->json(['error' => true, 'message' => 'Unauthorized access.'], 403);
            }
            
            $mapping = $request->mapping_id 
                ? \App\Models\MappingConfiguration::findOrFail($request->mapping_id)
                : null;

            // Pastikan department_id terisi dengan benar
            $departmentId = $user->isAdmin() ? $format->department_id : $user->department_id;
            
            if (!$departmentId) {
                return response()->json(['error' => true, 'message' => 'Department ID tidak valid. Hubungi administrator.'], 400);
            }
            
            Log::info('Starting upload process', [
                'user_id' => $user->id,
                'user_department_id' => $user->department_id,
                'format_id' => $format->id,
                'format_department_id' => $format->department_id,
                'final_department_id' => $departmentId,
                'mapping_id' => $mapping?->id
            ]);

            // Get upload mode from request (default to 'append')
            $uploadMode = $request->input('upload_mode', 'append');

            // Simpan file lalu antrikan job
            $storedPath = $file->store('uploads/sellout');
            $originalFilename = $file->getClientOriginalName();

            ProcessSelloutImportJob::dispatch(
                $storedPath,
                $originalFilename,
                $format->id,
                $mapping?->id,
                $departmentId,
                $user->id,
                $uploadMode
            );

            return response()->json([
                'queued' => true,
                'message' => 'File accepted and queued for processing.',
                'redirect' => route('history.index')
            ]);
                
        } catch (\Throwable $e) {
            Log::error('Upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error'   => true,
                'message' => 'Terjadi kesalahan saat memproses file. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }

    /**
     * Baca header dan sample baris (maks 50) tanpa memuat seluruh file ke memori.
     */
    private function extractPreviewRows($file, int $maxRows = 4): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        // CSV: baca cepat dengan fgetcsv dan batas baris
        if ($ext === 'csv') {
            $path = $file->getRealPath();
            $rows = [];
            if (($handle = fopen($path, 'r')) !== false) {
                $count = 0;
                while (($row = fgetcsv($handle)) !== false && $count < $maxRows) {
                    $rows[] = $row;
                    $count++;
                }
                fclose($handle);
            }

            $headers = collect($rows[0] ?? [])->map(fn($h) => trim((string) $h))->filter()->values()->all();
            $sample = collect($rows)->slice(1)->take(3)->map(fn($r) => array_values($r))->values()->all();

            return [$headers, $sample];
        }

        // XLS/XLSX: pakai PhpSpreadsheet ReadFilter, hanya baca maksimal $maxRows
        // Gunakan disk caching supaya tidak habiskan memori
        $filter = new class($maxRows) implements IReadFilter {
            public function __construct(private int $maxRows) {}
            public function readCell($column, $row, $worksheetName = ''): bool
            {
                return $row <= $this->maxRows;
            }
        };

        // Catatan: Versi PhpSpreadsheet yang terpasang tidak mendukung pengaturan cache storage,
        // jadi kita hanya gunakan read filter + readDataOnly untuk membatasi memori.

        $reader = IOFactory::createReaderForFile($file->getPathname());
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($filter);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        $spreadsheet = $reader->load($file->getPathname());
        $sheet = $spreadsheet->getSheet(0);

        $rows = [];
        $rowIterator = $sheet->getRowIterator(1, $maxRows);
        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            if (method_exists($cellIterator, 'setIterateOnlyExistingCells')) {
                $cellIterator->setIterateOnlyExistingCells(true);
            }
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string) $cell->getValue());
            }
            $rows[] = $rowData;
            if (count($rows) >= $maxRows) {
                break;
            }
        }

        $headers = collect($rows[0] ?? [])->filter(fn($h) => $h !== '')->values()->all();
        $sample = collect($rows)->slice(1)->take(3)->map(function ($row) {
            return array_values($row);
        })->all();

        // Bebaskan memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [$headers, $sample];
    }
}
