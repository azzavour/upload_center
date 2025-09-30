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
    /**
     * @var UploadService
     */
    protected $uploadService;
    
    /**
     * @var ExcelFormatService
     */
    protected $formatService;
    
    /**
     * @var MappingService
     */
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
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $formats = $this->formatService->getAllFormats($departmentId);
        return view('upload.index', compact('formats'));
    }

    public function checkFormat(Request $request)
    {
        // Validasi sederhana berdasarkan extension saja
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
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses department
            /** @var \App\Models\User $user */
            $user = Auth::user();
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

            $departmentId = $user->isAdmin() ? null : $user->department_id;
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
                $dbColumn = collect($mappingToUse)->search(function ($dbCol, $excelCol) use ($header) {
                    return strtolower(trim($excelCol)) === strtolower($header);
                });

                if ($dbColumn !== false) {
                    $headerAnalysis[] = [
                        'name' => $header,
                        'status' => 'mapped',
                        'mapped_to' => $mappingToUse[$dbColumn]
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
            Log::error('Upload check error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        // Validasi sederhana berdasarkan extension saja
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
            
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses
            if (!$user->isAdmin() && $format->department_id !== $user->department_id) {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }
            
            $mapping = $request->mapping_id 
                ? \App\Models\MappingConfiguration::findOrFail($request->mapping_id)
                : null;

            $history = $this->uploadService->processUpload(
                $request->file('file'),
                $format,
                $mapping,
                $user->department_id,
                $user->id
            );

            return redirect()->route('history.show', $history->id)
                ->with('success', 'File berhasil diupload dan diproses!');
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal upload file: ' . $e->getMessage());
        }
    }
}