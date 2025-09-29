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
        $this->uploadService = $uploadService;
        $this->formatService = $formatService;
        $this->mappingService = $mappingService;
    }

    public function index()
    {
        $formats = $this->formatService->getAllFormats();
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

            // Baca header Excel
            $data = Excel::toArray([], $file);
            
            // Validasi jika file kosong
            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'error' => true,
                    'message' => 'File Excel kosong atau tidak valid'
                ], 400);
            }
            
            $headers = $data[0][0] ?? [];

            // Validasi jika tidak ada header
            if (empty($headers)) {
                return response()->json([
                    'error' => true,
                    'message' => 'File Excel tidak memiliki header'
                ], 400);
            }

            // Normalize headers: trim whitespace dan filter kolom kosong
            $headers = array_map('trim', $headers);
            $headers = array_filter($headers, function($header) {
                return $header !== '';
            });
            $headers = array_values($headers); // Re-index array setelah filter

            // STEP 1: Cek apakah ada mapping yang cocok dengan struktur file ini
            $existingMapping = $this->mappingService->findMappingByExcelColumns($format->id, $headers);

            if ($existingMapping) {
                // Hitung kolom yang akan diabaikan
                $mappingColumns = array_keys($existingMapping->column_mapping);
                $mappingColumnsNormalized = array_map(function($col) {
                    return strtolower(trim($col));
                }, $mappingColumns);
                
                $headersNormalized = array_map('strtolower', $headers);
                $ignoredColumns = array_diff($headersNormalized, $mappingColumnsNormalized);
                
                $message = 'Format ditemukan dengan mapping: ' . $existingMapping->mapping_index;
                if (count($ignoredColumns) > 0) {
                    $message .= ' (Kolom diabaikan: ' . implode(', ', array_values($ignoredColumns)) . ')';
                }
                
                // Mapping sudah ada! Bisa langsung upload
                return response()->json([
                    'is_new_format' => false,
                    'has_mapping' => true,
                    'mapping_id' => $existingMapping->id,
                    'mapping_index' => $existingMapping->mapping_index,
                    'can_proceed' => true,
                    'message' => $message
                ], 200);
            }

            // STEP 2: Jika tidak ada mapping, cek apakah ini format standar (expected_columns)
            $isStandardFormat = $this->formatService->isStandardFormat($headers, $format);

            if ($isStandardFormat) {
                // Format standar, tidak perlu mapping
                return response()->json([
                    'is_new_format' => false,
                    'has_mapping' => false,
                    'can_proceed' => true,
                    'message' => 'Format standar terdeteksi. Anda dapat melanjutkan upload.'
                ], 200);
            }

            // STEP 3: Format baru terdeteksi, perlu buat mapping
            // Simpan headers ke session untuk digunakan di mapping
            session([
                'excel_columns' => $headers, 
                'temp_format_id' => $format->id
            ]);

            return response()->json([
                'is_new_format' => true,
                'excel_columns' => $headers,
                'expected_columns' => $format->expected_columns,
                'message' => 'Format baru terdeteksi. Silakan buat mapping terlebih dahulu.',
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
            $format = $this->formatService->findFormatById($request->format_id);
            $mapping = $request->mapping_id 
                ? \App\Models\MappingConfiguration::findOrFail($request->mapping_id)
                : null;

            $history = $this->uploadService->processUpload(
                $request->file('file'),
                $format,
                $mapping
            );

            return redirect()->route('history.show', $history->id)
                ->with('success', 'File berhasil diupload dan diproses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal upload file: ' . $e->getMessage());
        }
    }
}