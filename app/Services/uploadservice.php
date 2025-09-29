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

    public function __construct(MappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    public function processUpload($file, ExcelFormat $format, ?MappingConfiguration $mapping = null)
    {
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = time() . '_' . $originalFilename;
        
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
            'uploaded_by' => null // Set null jika tidak ada auth
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
        $data = Excel::toArray([], storage_path('app/' . $path));
        $rows = $data[0]; // Ambil sheet pertama
        
        // Ambil header (baris pertama)
        $headers = array_shift($rows);
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                // Skip baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Kombinasikan header dengan data
                    $rowData = array_combine($headers, $row);
                    
                    // Apply mapping jika ada
                    if ($mapping) {
                        $rowData = $this->mappingService->applyMapping($rowData, $mapping->column_mapping);
                        
                        // Apply transformation rules
                        if ($mapping->transformation_rules) {
                            $rowData = $this->applyTransformations($rowData, $mapping->transformation_rules);
                        }
                    }
                    
                    // Transformasi khusus untuk tracks
                    $rowData = $this->transformTrackData($rowData);
                    $rowData['upload_history_id'] = $history->id;
                    
                    DB::table($format->target_table)->insert($rowData);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $index + 2, // +2 karena header dan index mulai dari 0
                        'data' => $rowData ?? [],
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            // Update history
            $history->update([
                'total_rows' => count($rows),
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'error_details' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function transformTrackData(array $data)
    {
        // Transform Release Date
        if (isset($data['release_date']) && !empty($data['release_date'])) {
            try {
                if (is_numeric($data['release_date'])) {
                    // Excel serial date
                    $data['release_date'] = Carbon::createFromFormat('Y-m-d', '1900-01-01')
                        ->addDays($data['release_date'] - 2)
                        ->format('Y-m-d');
                } else {
                    // String date
                    $data['release_date'] = Carbon::parse($data['release_date'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $data['release_date'] = null;
            }
        }
        
        // Transform Price - remove currency symbols
        if (isset($data['track_price'])) {
            $data['track_price'] = preg_replace('/[^0-9.]/', '', $data['track_price']);
            $data['track_price'] = $data['track_price'] ?: null;
        }
        
        if (isset($data['collection_price'])) {
            $data['collection_price'] = preg_replace('/[^0-9.]/', '', $data['collection_price']);
            $data['collection_price'] = $data['collection_price'] ?: null;
        }
        
        // Normalize country code
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
                        // Keep original value if parsing fails
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