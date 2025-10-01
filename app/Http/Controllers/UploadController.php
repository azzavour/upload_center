<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use App\Services\ExcelFormatService;
use App\Services\MappingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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

        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = ['xlsx', 'xls', 'csv'];
        
        if (!in_array($extension, $validExtensions)) {
            return response()->json([
                'error' => true, 
                'message' => 'File harus berformat XLSX, XLS, atau CSV. Extension yang terdeteksi: ' . $extension
            ], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json(['error' => true, 'message' => 'Ukuran file maksimal 10MB'], 400);
        }

        if (!$request->format_id) {
            return response()->json(['error' => true, 'message' => 'Format ID tidak ditemukan'], 400);
        }

        try {
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

            $data = Excel::toArray([], $file);
            if (empty($data) || empty($data[0])) {
                return response()->json(['error' => true, 'message' => 'File Excel kosong atau tidak valid'], 400);
            }
            
            $excelHeaders = collect($data[0][0] ?? [])->map(fn($h) => trim($h))->filter()->values()->all();

            if (empty($excelHeaders)) {
                return response()->json(['error' => true, 'message' => 'File Excel tidak memiliki header'], 400);
            }

            $departmentId = $user->isAdmin() ? $format->department_id : $user->department_id;
            
            Log::info('Checking format for department', [
                'format_id' => $format->id,
                'department_id' => $departmentId,
                'excel_headers' => $excelHeaders
            ]);
            
            $existingMapping = $this->mappingService->findMappingByExcelColumns($format->id, $excelHeaders, $departmentId);

            $sampleData = collect($data[0])->slice(1)->take(3)->values()->all();

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
        $file = $request->file('file');
        
        if (!$file) {
            return redirect()->back()->with('error', 'File tidak ditemukan');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = ['xlsx', 'xls', 'csv'];
        
        if (!in_array($extension, $validExtensions)) {
            return redirect()->back()->with('error', 'File harus berformat XLSX, XLS, atau CSV');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file maksimal 10MB');
        }

        if (!$request->format_id) {
            return redirect()->back()->with('error', 'Format Excel wajib dipilih');
        }

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // ✅ Validasi department
            if (!$user->hasDepartment() && !$user->isAdmin()) {
                return redirect()->back()->with('error', 'Anda belum terdaftar di department manapun. Hubungi administrator.');
            }
            
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses
            if (!$user->isAdmin() && $format->department_id !== $user->department_id) {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }
            
            $mapping = $request->mapping_id 
                ? \App\Models\MappingConfiguration::findOrFail($request->mapping_id)
                : null;

            // ✅ CRITICAL: Pastikan department_id terisi dengan benar
            $departmentId = $user->isAdmin() ? $format->department_id : $user->department_id;
            
            if (!$departmentId) {
                return redirect()->back()->with('error', 'Department ID tidak valid. Hubungi administrator.');
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
            
            $history = $this->uploadService->processUpload(
                $request->file('file'),
                $format,
                $mapping,
                $departmentId, // ✅ Pastikan ini tidak null
                $user->id,
                $uploadMode
            );

            return redirect()->route('history.show', $history->id)
                ->with('success', 'File berhasil diupload dan diproses!');
                
        } catch (\Exception $e) {
            Log::error('Upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal upload file: ' . $e->getMessage());
        }
    }
}