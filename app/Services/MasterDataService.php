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

        // Batch insert ke master_data
        $masterRecords = [];
        foreach ($data as $row) {
            $masterRecords[] = [
                'department_id' => $departmentId,
                'upload_history_id' => $history->id,
                'source_table' => $actualTableName, // ✅ Simpan actual table name
                'data' => json_encode($row),
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
            // ✅ PERBAIKAN: Gunakan actual table name
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
}