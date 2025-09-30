<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use App\Services\ExcelFormatService;
use App\Services\MappingService;
use Illuminate\Http\Request;
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
        $user = auth()->user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $formats = $this->formatService->getAllFormats($departmentId);
        return view('upload.index', compact('formats'));
    }

    public function checkFormat(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'format_id' => 'required|exists:excel_formats,id'
        ]);

        try {
            $file = $request->file('file');
            $format = $this->formatService->findFormatById($request->format_id);
            
            // Cek akses department
            $user = auth()->user();
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
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'format_id' => 'required|exists:excel_formats,id',
            'mapping_id' => 'nullable|exists:mapping_configurations,id'
        ]);

        try {
            $user = auth()->user();
            
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
            return redirect()->back()
                ->with('error', 'Gagal upload file: ' . $e->getMessage());
        }
    }
}