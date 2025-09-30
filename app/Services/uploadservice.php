<?php

namespace App\Services;

use App\Models\UploadHistory;
use App\Models\ExcelFormat;
use App\Models\MappingConfiguration;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UploadService
{
    protected $mappingService;
    protected $masterDataService;

    public function __construct(
        MappingService $mappingService,
        MasterDataService $masterDataService
    ) {
        $this->mappingService = $mappingService;
        $this->masterDataService = $masterDataService;
    }

    public function processUpload(
        $file, 
        ExcelFormat $format, 
        ?MappingConfiguration $mapping = null,
        int $departmentId = null,
        int $userId = null
    ) {
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = time() . '_' . $originalFilename;
        
        $uploadDir = storage_path('app/uploads');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $path = $file->storeAs('uploads', $storedFilename);

        $history = UploadHistory::create([
            'excel_format_id' => $format->id,
            'mapping_configuration_id' => $mapping?->id,
            'department_id' => $departmentId,
            'uploaded_by' => $userId,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'status' => 'pending',
            'uploaded_at' => now()
        ]);

        try {
            $history->update(['status' => 'processing']);
            $this->importData($path, $format, $mapping, $history);
            $history->update(['status' => 'completed']);
            
            // Sinkronisasi ke master data
            $this->masterDataService->syncToMasterData($history);
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
        if (!Storage::exists($path)) {
            throw new \Exception('File ' . $path . ' tidak ditemukan di dalam storage.');
        }

        $data = Excel::toArray([], $path);
        $rows = $data[0];
        
        $headers = array_shift($rows);
        $headers = array_map('trim', $headers);
        $headers = array_filter($headers, fn($h) => $h !== '');
        $headers = array_values($headers);
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $warnings = [];

        $validColumns = $this->getTableColumns($format->target_table);

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

                    $originalKeys = array_keys($rowData);
                    $filteredData = array_intersect_key($rowData, array_flip($validColumns));

                    $ignoredColumns = array_diff($originalKeys, $validColumns);

                    if (!empty($ignoredColumns)) {
                        $warnings[] = [
                            'row' => $index + 2,
                            'ignored_columns' => array_values($ignoredColumns)
                        ];
                    }

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

    public function getUploadHistory(int $departmentId = null)
    {
        $query = UploadHistory::with(['excelFormat', 'mappingConfiguration', 'uploader']);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->orderBy('uploaded_at', 'desc')->get();
    }

    public function getUploadById(int $id, int $departmentId = null)
    {
        $query = UploadHistory::with(['excelFormat', 'mappingConfiguration', 'uploader']);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->findOrFail($id);
    }
}