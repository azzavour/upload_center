<?php

namespace App\Services;

use App\Models\UploadHistory;
use App\Models\ExcelFormat;
use App\Models\MappingConfiguration;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UploadService
{
    protected $mappingService;

    public function __construct(MappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    public function processUpload($file, ExcelFormat $format, ?MappingConfiguration $mapping = null)
    {
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = time() . '_' . $originalFilename;
        
        // PASTIKAN folder uploads ada
        $uploadDir = storage_path('app/uploads');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Simpan file
        $path = $file->storeAs('uploads', $storedFilename);

        // Buat history record
        $history = UploadHistory::create([
            'excel_format_id' => $format->id,
            'mapping_configuration_id' => $mapping?->id,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'status' => 'pending',
            'uploaded_at' => now(),
            'uploaded_by' => null
        ]);

        // Process data
        try {
            $history->update(['status' => 'processing']);
            $this->importData($path, $format, $mapping, $history);
            $history->update(['status' => 'completed']);
        } catch (\Exception $e) {
            $history->update([
                'status' => 'failed',
                'error_details' => ['message' => $e->getMessage()]
            ]);
            
            throw $e;
        }

        return $history;
    }

    protected function importData($path, ExcelFormat $format, ?MappingConfiguration $mapping, UploadHistory $history)
    {
        $fullPath = storage_path('app/' . $path);
        
        if (!file_exists($fullPath)) {
            throw new \Exception('File tidak ditemukan: ' . $fullPath);
        }
        
        $data = Excel::toArray([], $fullPath);
        $rows = $data[0];
        
        $headers = array_shift($rows);
        $headers = array_map('trim', $headers);
        $headers = array_filter($headers, fn($h) => $h !== '');
        $headers = array_values($headers);
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $warnings = []; // <-- tambahan untuk catat warning

        // Ambil kolom valid dari tabel target (sekali saja)
        $validColumns = $this->getTableColumns($format->target_table);

        // Kolom wajib (sesuaikan dengan kebutuhan bisnismu)
        $requiredColumns = ['track_id', 'track_name', 'artist_id', 'artist_name'];

        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    $rowData = array_combine($headers, array_slice($row, 0, count($headers)));
                    
                    if ($mapping) {
                        $rowData = $this->mappingService->applyMapping($rowData, $mapping->column_mapping);
                        
                        if ($mapping->transformation_rules) {
                            $rowData = $this->applyTransformations($rowData, $mapping->transformation_rules);
                        }
                    }
                    
                    $rowData = $this->transformTrackData($rowData);
                    $rowData['upload_history_id'] = $history->id;

                    // === PERUBAHAN UTAMA DI SINI ===
                    $originalKeys = array_keys($rowData);
                    $filteredData = array_intersect_key($rowData, array_flip($validColumns));

                    // Cek kolom wajib yang hilang
                    $missingRequired = array_diff($requiredColumns, array_keys($filteredData));
                    // Cek kolom berlebih yang diabaikan
                    $ignoredColumns = array_diff($originalKeys, $validColumns);

                    // Catat warning jika ada
                    if (!empty($missingRequired) || !empty($ignoredColumns)) {
                        $warnings[] = [
                            'row' => $index + 2,
                            'missing_required' => array_values($missingRequired),
                            'ignored_columns' => array_values($ignoredColumns)
                        ];
                    }

                    // Insert hanya jika ada data valid
                    if (!empty($filteredData)) {
                        DB::table($format->target_table)->insert($filteredData);
                        $successCount++;
                    } else {
                        throw new \Exception('Tidak ada kolom valid untuk di-insert');
                    }

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $index + 2,
                        'data' => $rowData ?? [],
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            // Gabungkan errors dan warnings dalam error_details
            $history->update([
                'total_rows' => count($rows),
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'error_details' => array_merge(
                    $errors,
                    array_map(fn($w) => ['type' => 'warning'] + $w, $warnings)
                )
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getTableColumns(string $tableName): array
{
    // PostgreSQL: table_name di information_schema selalu lowercase
    $lowerTableName = strtolower($tableName);
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ?", [$lowerTableName]);
    return collect($columns)->pluck('column_name')->toArray();
}

    protected function transformTrackData(array $data)
    {
        if (isset($data['release_date']) && !empty($data['release_date'])) {
            try {
                if (is_numeric($data['release_date'])) {
                    $data['release_date'] = Carbon::createFromFormat('Y-m-d', '1900-01-01')
                        ->addDays($data['release_date'] - 2)
                        ->format('Y-m-d');
                } else {
                    $data['release_date'] = Carbon::parse($data['release_date'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $data['release_date'] = null;
            }
        }
        
        if (isset($data['track_price'])) {
            $data['track_price'] = preg_replace('/[^0-9.]/', '', $data['track_price']);
            $data['track_price'] = $data['track_price'] ?: null;
        }
        
        if (isset($data['collection_price'])) {
            $data['collection_price'] = preg_replace('/[^0-9.]/', '', $data['collection_price']);
            $data['collection_price'] = $data['collection_price'] ?: null;
        }
        
        if (isset($data['country'])) {
            $data['country'] = strtoupper(substr($data['country'], 0, 10));
        }
        
        return $data;
    }

    protected function applyTransformations(array $data, array $rules)
    {
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) || empty($rule['type'])) continue;
            
            switch ($rule['type']) {
                case 'uppercase':
                    $data[$field] = strtoupper($data[$field]);
                    break;
                case 'lowercase':
                    $data[$field] = strtolower($data[$field]);
                    break;
                case 'trim':
                    $data[$field] = trim($data[$field]);
                    break;
                case 'date_format':
                    try {
                        $data[$field] = Carbon::parse($data[$field])
                            ->format($rule['format'] ?? 'Y-m-d');
                    } catch (\Exception $e) {
                        // Keep original
                    }
                    break;
            }
        }
        
        return $data;
    }

    public function getUploadHistory()
    {
        return UploadHistory::with(['excelFormat', 'mappingConfiguration'])
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    public function getUploadById(int $id)
    {
        return UploadHistory::with(['excelFormat', 'mappingConfiguration'])
            ->findOrFail($id);
    }
}