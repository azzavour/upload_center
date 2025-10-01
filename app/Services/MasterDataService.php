<?php

namespace App\Services;

use App\Models\MasterData;
use App\Models\UploadHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterDataService
{
    protected $tableManager;

    public function __construct(TableManagerService $tableManager)
    {
        $this->tableManager = $tableManager;
    }

    /**
     * ✅ FIXED: Sinkronisasi data dari tabel department ke master_data
     * Dipanggil setelah upload berhasil
     */
    public function syncToMasterData(UploadHistory $history)
    {
        $baseTableName = $history->excelFormat->target_table;
        $departmentId = $history->department_id;

        // ✅ PERBAIKAN: Gunakan actual table name dengan prefix department
        $actualTableName = $this->tableManager->getActualTableName($baseTableName, $departmentId);

        Log::info('Syncing to master_data', [
            'history_id' => $history->id,
            'base_table' => $baseTableName,
            'actual_table' => $actualTableName,
            'department_id' => $departmentId
        ]);

        // Validasi tabel exists
        if (!$this->tableManager->tableExists($baseTableName, $departmentId)) {
            Log::error('Table does not exist for sync', [
                'table' => $actualTableName
            ]);
            throw new \Exception("Table {$actualTableName} does not exist");
        }

        // Ambil data yang baru di-insert (berdasarkan upload_history_id)
        $data = DB::table($actualTableName)
            ->where('upload_history_id', $history->id)
            ->get();

        Log::info('Data retrieved for sync', [
            'table' => $actualTableName,
            'count' => $data->count()
        ]);

        // ✅ CRITICAL FIX: Convert object to array dan handle datetime properly
        $masterRecords = [];
        foreach ($data as $row) {
            // Convert stdClass object to array
            $rowArray = (array) $row;
            
            // ✅ FIX: Format datetime fields properly sebelum json_encode
            if (isset($rowArray['created_at'])) {
                $rowArray['created_at'] = $this->formatDatetime($rowArray['created_at']);
            }
            if (isset($rowArray['updated_at'])) {
                $rowArray['updated_at'] = $this->formatDatetime($rowArray['updated_at']);
            }
            
            // Remove binary/non-serializable data jika ada
            $cleanedRow = $this->cleanRowData($rowArray);
            
            $masterRecords[] = [
                'department_id' => $departmentId,
                'upload_history_id' => $history->id,
                'source_table' => $actualTableName,
                'data' => json_encode($cleanedRow, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), // ✅ Better encoding
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($masterRecords)) {
            // Insert in chunks untuk performa
            foreach (array_chunk($masterRecords, 500) as $chunk) {
                DB::table('master_data')->insert($chunk);
            }
            
            Log::info('Master data sync completed', [
                'records_inserted' => count($masterRecords)
            ]);
        }

        return count($masterRecords);
    }

    /**
     * ✅ NEW: Format datetime to ISO 8601 string
     */
    private function formatDatetime($datetime)
    {
        if (is_null($datetime)) {
            return null;
        }
        
        try {
            // Handle different datetime formats
            if ($datetime instanceof \DateTime) {
                return $datetime->format('Y-m-d H:i:s');
            }
            
            if (is_string($datetime)) {
                $dt = new \DateTime($datetime);
                return $dt->format('Y-m-d H:i:s');
            }
            
            return $datetime;
        } catch (\Exception $e) {
            Log::warning('Failed to format datetime', [
                'value' => $datetime,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ NEW: Clean row data dari binary/non-serializable content
     */
    private function cleanRowData(array $row): array
    {
        $cleaned = [];
        
        foreach ($row as $key => $value) {
            // Skip binary data atau resource
            if (is_resource($value)) {
                continue;
            }
            
            // Convert boolean to int untuk JSON consistency
            if (is_bool($value)) {
                $cleaned[$key] = $value ? 1 : 0;
                continue;
            }
            
            // Handle null values
            if (is_null($value)) {
                $cleaned[$key] = null;
                continue;
            }
            
            // Keep scalar values
            if (is_scalar($value)) {
                $cleaned[$key] = $value;
                continue;
            }
            
            // Convert objects to string representation
            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $cleaned[$key] = (string) $value;
                } else {
                    $cleaned[$key] = json_encode($value);
                }
                continue;
            }
            
            // Handle arrays recursively
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanRowData($value);
                continue;
            }
            
            // Default: keep as is
            $cleaned[$key] = $value;
        }
        
        return $cleaned;
    }

    /**
     * Ambil semua data master untuk admin
     */
    public function getAllMasterData($filters = [])
    {
        $query = MasterData::with(['department', 'uploadHistory.uploader']);

        // Filter by department
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Filter by source table
        if (isset($filters['source_table'])) {
            $query->where('source_table', 'LIKE', '%' . $filters['source_table'] . '%');
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Export master data ke CSV untuk admin
     */
    public function exportMasterDataToCsv($filters = [])
    {
        $data = $this->getAllMasterData($filters)->items();
        
        if (empty($data)) {
            return null;
        }

        $filename = 'master_data_' . date('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);

        // Ensure exports directory exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $file = fopen($path, 'w');
        
        // Header
        fputcsv($file, [
            'ID',
            'Department',
            'Source Table',
            'Uploaded By',
            'Upload Date',
            'Data'
        ]);

        // Rows
        foreach ($data as $record) {
            fputcsv($file, [
                $record->id,
                $record->department->name ?? 'N/A',
                $record->source_table,
                $record->uploadHistory->uploader->name ?? 'N/A',
                $record->created_at->format('Y-m-d H:i:s'),
                json_encode($record->data)
            ]);
        }

        fclose($file);

        return $filename;
    }

    /**
     * Deteksi duplikasi tabel dengan nama berbeda
     * Membandingkan struktur kolom tabel
     */
    public function detectDuplicateTables(): array
    {
        $formats = DB::table('excel_formats')
            ->whereNotNull('department_id')
            ->get();

        $duplicates = [];
        $tableStructures = [];

        foreach ($formats as $format) {
            $department = DB::table('departments')->find($format->department_id);
            if (!$department) {
                continue;
            }

            $deptCode = strtolower($department->code);
            $actualTableName = "dept_{$deptCode}_{$format->target_table}";

            // Check if table exists
            $tableExists = DB::select("
                SELECT tablename 
                FROM pg_tables 
                WHERE schemaname = 'public' 
                AND tablename = ?
            ", [$actualTableName]);

            if (empty($tableExists)) {
                continue;
            }

            $columns = DB::select("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = ? 
                AND column_name NOT IN ('id', 'upload_history_id', 'department_id', 'created_at', 'updated_at')
                ORDER BY column_name
            ", [$actualTableName]);

            $columnNames = array_map(fn($c) => $c->column_name, $columns);
            $signature = implode(',', $columnNames);

            if (isset($tableStructures[$signature])) {
                $duplicates[] = [
                    'original_table' => $tableStructures[$signature]['table'],
                    'original_department' => $tableStructures[$signature]['department'],
                    'duplicate_table' => $actualTableName,
                    'duplicate_department' => $department->name,
                    'columns' => $columnNames
                ];
            } else {
                $tableStructures[$signature] = [
                    'table' => $actualTableName,
                    'department' => $department->name
                ];
            }
        }

        return $duplicates;
    }

    /**
     * ✅ NEW: Validate and repair corrupted master_data
     */
    public function repairCorruptedMasterData(): array
    {
        $repaired = 0;
        $failed = 0;
        
        $corruptedRecords = DB::table('master_data')
            ->whereRaw("data::text NOT LIKE '%\"created_at\":\"%-%-%'")
            ->orWhereRaw("data::text LIKE '%2000:00.%'")
            ->get();
        
        foreach ($corruptedRecords as $record) {
            try {
                $data = json_decode($record->data, true);
                
                if (!$data) {
                    $failed++;
                    continue;
                }
                
                // Fix datetime fields
                if (isset($data['created_at'])) {
                    $data['created_at'] = $this->formatDatetime($data['created_at']);
                }
                if (isset($data['updated_at'])) {
                    $data['updated_at'] = $this->formatDatetime($data['updated_at']);
                }
                
                // Update record
                DB::table('master_data')
                    ->where('id', $record->id)
                    ->update([
                        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    ]);
                
                $repaired++;
            } catch (\Exception $e) {
                Log::error('Failed to repair master_data record', [
                    'id' => $record->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }
        
        return [
            'total_corrupted' => count($corruptedRecords),
            'repaired' => $repaired,
            'failed' => $failed
        ];
    }
}