<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class TableManagerService
{
    /**
     * Buat tabel baru berdasarkan kolom yang ditentukan user
     */
    public function createDynamicTable(string $tableName, array $columns)
    {
        // Normalisasi nama tabel: lowercase, replace spaces with underscore
        $tableName = $this->normalizeTableName($tableName);

        // Cek apakah tabel sudah ada
        if (Schema::hasTable($tableName)) {
            throw new \Exception("Tabel '$tableName' sudah ada. Gunakan nama tabel lain.");
        }

        Schema::create($tableName, function (Blueprint $table) use ($columns) {
            $table->id();
            
            // Kolom wajib untuk tracking
            $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
            
            // Kolom dinamis dari user (semua varchar)
            foreach ($columns as $column) {
                $normalizedColumn = $this->normalizeColumnName($column);
                $table->string($normalizedColumn, 500)->nullable();
            }
            
            $table->timestamps();
        });

        return $tableName;
    }

    /**
     * Validasi apakah tabel sudah ada
     */
    public function tableExists(string $tableName): bool
    {
        return Schema::hasTable($this->normalizeTableName($tableName));
    }

    /**
     * Ambil list kolom dari tabel
     */
    public function getTableColumns(string $tableName): array
    {
        $tableName = $this->normalizeTableName($tableName);
        
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return Schema::getColumnListing($tableName);
    }

    /**
     * Normalisasi nama tabel
     * - Lowercase
     * - Replace spaces dengan underscore
     * - Remove special chars kecuali underscore
     */
    public function normalizeTableName(string $tableName): string
    {
        $tableName = strtolower(trim($tableName));
        $tableName = preg_replace('/\s+/', '_', $tableName); // spaces to underscore
        $tableName = preg_replace('/[^a-z0-9_]/', '', $tableName); // remove special chars
        
        // Pastikan tidak diawali dengan angka
        if (preg_match('/^[0-9]/', $tableName)) {
            $tableName = 'tbl_' . $tableName;
        }
        
        return $tableName;
    }

    /**
     * Normalisasi nama kolom
     * - Lowercase
     * - Replace spaces dengan underscore
     * - Remove special chars
     */
    public function normalizeColumnName(string $columnName): string
    {
        $columnName = strtolower(trim($columnName));
        $columnName = preg_replace('/\s+/', '_', $columnName);
        $columnName = preg_replace('/[^a-z0-9_]/', '', $columnName);
        
        // Hindari reserved keywords PostgreSQL
        $reserved = ['id', 'created_at', 'updated_at', 'deleted_at', 'user', 'group', 'order'];
        if (in_array($columnName, $reserved)) {
            $columnName = 'col_' . $columnName;
        }
        
        return $columnName;
    }

    /**
     * Ambil semua tabel yang dibuat oleh department tertentu
     */
    public function getDepartmentTables(int $departmentId): array
    {
        // Query dari excel_formats untuk mendapatkan target_table yang unique
        return DB::table('excel_formats')
            ->where('department_id', $departmentId)
            ->distinct()
            ->pluck('target_table')
            ->toArray();
    }
}