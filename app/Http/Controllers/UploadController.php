<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use App\Services\ExcelFormatService;
use App\Services\MappingService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;

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

        // --- Validasi dan Pembacaan Header (dari kode Anda) ---
        $data = Excel::toArray([], $file);
        if (empty($data) || empty($data[0])) {
            return response()->json(['error' => true, 'message' => 'File Excel kosong atau tidak valid'], 400);
        }
        
        // Normalisasi header (dari kode Anda, ini sudah bagus)
        $excelHeaders = collect($data[0][0] ?? [])->map(fn($h) => trim($h))->filter()->values()->all();

        if (empty($excelHeaders)) {
            return response()->json(['error' => true, 'message' => 'File Excel tidak memiliki header'], 400);
        }

        // --- Logika Inti (Gabungan) ---

        // Cek mapping yang sudah ada
        $existingMapping = $this->mappingService->findMappingByExcelColumns($format->id, $excelHeaders);

        // Apapun hasilnya (ada mapping, standar, atau baru), kita siapkan data pratinjau
        // Ambil 3 baris data pertama sebagai sampel (baris 2, 3, 4 di Excel)
        $sampleData = collect($data[0])->slice(1)->take(3)->values()->all();

        // Siapkan variabel untuk analisis header
        $headerAnalysis = [];
        $mappingToUse = $existingMapping ? $existingMapping->column_mapping : [];

        // Jika tidak ada mapping custom, coba gunakan format standar sebagai dasar analisis
        if (empty($mappingToUse) && $this->formatService->isStandardFormat($excelHeaders, $format)) {
            foreach ($format->expected_columns as $col) {
                $mappingToUse[$col] = $col;
            }
        }
        
        // Lakukan analisis header berdasarkan mapping yang akan digunakan
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
        
        // Buat data pratinjau untuk dikirim ke frontend
        $previewPayload = [
            'headers' => $headerAnalysis,
            'data' => $sampleData
        ];

        // --- Kondisi dan Respons (dari kode Anda, dengan penambahan data pratinjau) ---

        if ($existingMapping) {
            return response()->json([
                'is_new_format' => false,
                'has_mapping' => true,
                'mapping_id' => $existingMapping->id,
                'can_proceed' => true,
                'message' => 'Format file valid dan mapping yang cocok ditemukan.',
                'preview' => $previewPayload // Tambahkan ini
            ], 200);
        }

        if ($this->formatService->isStandardFormat($excelHeaders, $format)) {
            return response()->json([
                'is_new_format' => false,
                'has_mapping' => false,
                'can_proceed' => true,
                'message' => 'Format standar terdeteksi. Silakan periksa pratinjau sebelum melanjutkan.',
                'preview' => $previewPayload // Tambahkan ini
            ], 200);
        }

        // Format baru, perlu buat mapping
        session(['excel_columns' => $excelHeaders]);

        return response()->json([
            'is_new_format' => true,
            'message' => 'Format baru terdeteksi. Anda akan diarahkan untuk membuat mapping.',
            'redirect' => route('mapping.create', ['format_id' => $format->id])
            // Pratinjau tidak dikirim di sini karena akan dialihkan
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