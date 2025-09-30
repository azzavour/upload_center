<?php

namespace App\Services;

use App\Models\MasterData;
use App\Models\UploadHistory;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    /**
     * Sinkronisasi data dari tabel department ke master_data
     * Dipanggil setelah upload berhasil
     */
    public function syncToMasterData(UploadHistory $history)
    {
        $sourceTable = $history->excelFormat->target_table;
        $departmentId = $history->department_id;

        // Ambil data yang baru di-insert (berdasarkan upload_history_id)
        $data = DB::table($sourceTable)
            ->where('upload_history_id', $history->id)
            ->get();

        // Batch insert ke master_data
        $masterRecords = [];
        foreach ($data as $row) {
            $masterRecords[] = [
                'department_id' => $departmentId,
                'upload_history_id' => $history->id,
                'source_table' => $sourceTable,
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
            $query->where('source_table', $filters['source_table']);
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
        $tables = DB::select("
            SELECT DISTINCT target_table, department_id 
            FROM excel_formats 
            WHERE department_id IS NOT NULL
        ");

        $duplicates = [];
        $tableStructures = [];

        foreach ($tables as $table) {
            $columns = DB::select("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = ? 
                AND column_name NOT IN ('id', 'upload_history_id', 'created_at', 'updated_at')
                ORDER BY column_name
            ", [strtolower($table->target_table)]);

            $columnNames = array_map(fn($c) => $c->column_name, $columns);
            $signature = implode(',', $columnNames);

            if (isset($tableStructures[$signature])) {
                $duplicates[] = [
                    'original_table' => $tableStructures[$signature],
                    'duplicate_table' => $table->target_table,
                    'department_id' => $table->department_id,
                    'columns' => $columnNames
                ];
            } else {
                $tableStructures[$signature] = $table->target_table;
            }
        }

        return $duplicates;
    }
}