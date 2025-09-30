<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use App\Models\Department;

class TableManagerService
{
    /**
     * Buat tabel baru berdasarkan kolom yang ditentukan user
     * Dengan prefix department untuk isolasi data
     */
    public function createDynamicTable(string $tableName, array $columns, ?int $departmentId = null)
    {
        // Normalisasi nama tabel dengan department prefix
        $tableName = $this->normalizeTableNameWithDepartment($tableName, $departmentId);

        // Cek apakah tabel sudah ada
        if (Schema::hasTable($tableName)) {
            return $tableName; // Return existing table name
        }

        Schema::create($tableName, function (Blueprint $table) use ($columns) {
            $table->id();
            
            // Kolom wajib untuk tracking
            $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            
            // Kolom dinamis dari user (semua varchar)
            foreach ($columns as $column) {
                $normalizedColumn = $this->normalizeColumnName($column);
                $table->string($normalizedColumn, 500)->nullable();
            }
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['department_id', 'upload_history_id']);
        });

        return $tableName;
    }

    /**
     * Validasi apakah tabel sudah ada
     */
    public function tableExists(string $tableName, ?int $departmentId = null): bool
    {
        $fullTableName = $this->normalizeTableNameWithDepartment($tableName, $departmentId);
        return Schema::hasTable($fullTableName);
    }

    /**
     * Ambil list kolom dari tabel
     */
    public function getTableColumns(string $tableName, ?int $departmentId = null): array
    {
        $fullTableName = $this->normalizeTableNameWithDepartment($tableName, $departmentId);
        
        if (!Schema::hasTable($fullTableName)) {
            return [];
        }

        return Schema::getColumnListing($fullTableName);
    }

    /**
     * Normalisasi nama tabel dengan department prefix
     * Format: dept_{department_code}_{table_name}
     */
    public function normalizeTableNameWithDepartment(string $tableName, ?int $departmentId = null): string
    {
        $baseTableName = $this->normalizeTableName($tableName);
        
        if ($departmentId) {
            $department = Department::find($departmentId);
            if ($department) {
                $deptCode = strtolower($department->code);
                return "dept_{$deptCode}_{$baseTableName}";
            }
        }
        
        // Jika tidak ada department ID, gunakan nama tabel biasa (untuk backward compatibility)
        return $baseTableName;
    }

    /**
     * Get actual table name untuk department tertentu
     */
    public function getActualTableName(string $baseTableName, int $departmentId): string
    {
        return $this->normalizeTableNameWithDepartment($baseTableName, $departmentId);
    }

    /**
     * Normalisasi nama tabel (tanpa department prefix)
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
     */
    public function normalizeColumnName(string $columnName): string
    {
        $columnName = strtolower(trim($columnName));
        $columnName = preg_replace('/\s+/', '_', $columnName);
        $columnName = preg_replace('/[^a-z0-9_]/', '', $columnName);
        
        // Hindari reserved keywords PostgreSQL
        $reserved = ['id', 'created_at', 'updated_at', 'deleted_at', 'user', 'group', 'order', 'index', 'table'];
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
        $department = Department::find($departmentId);
        if (!$department) {
            return [];
        }

        $deptCode = strtolower($department->code);
        $prefix = "dept_{$deptCode}_";
        
        // Query dari excel_formats untuk mendapatkan target_table yang unique
        $baseTables = DB::table('excel_formats')
            ->where('department_id', $departmentId)
            ->distinct()
            ->pluck('target_table')
            ->toArray();
        
        // Generate full table names dengan prefix
        $fullTableNames = [];
        foreach ($baseTables as $baseTable) {
            $fullTableNames[] = $this->normalizeTableNameWithDepartment($baseTable, $departmentId);
        }
        
        return $fullTableNames;
    }

    /**
     * Ambil semua tabel di database yang match pattern department
     */
    public function getAllDepartmentTablesInDB(int $departmentId): array
    {
        $department = Department::find($departmentId);
        if (!$department) {
            return [];
        }

        $deptCode = strtolower($department->code);
        $prefix = "dept_{$deptCode}_";
        
        // Query untuk PostgreSQL
        $tables = DB::select("
            SELECT tablename 
            FROM pg_tables 
            WHERE schemaname = 'public' 
            AND tablename LIKE ?
        ", [$prefix . '%']);
        
        return array_map(fn($t) => $t->tablename, $tables);
    }

    /**
     * Drop table jika ada (hati-hati!)
     */
    public function dropTableIfExists(string $tableName, ?int $departmentId = null): bool
    {
        $fullTableName = $this->normalizeTableNameWithDepartment($tableName, $departmentId);
        
        if (Schema::hasTable($fullTableName)) {
            Schema::dropIfExists($fullTableName);
            return true;
        }
        
        return false;
    }

    /**
     * Copy struktur tabel ke department lain (untuk migrasi)
     */
    public function copyTableStructure(string $sourceTable, int $sourceDeptId, int $targetDeptId): string
    {
        $sourceTableFull = $this->normalizeTableNameWithDepartment($sourceTable, $sourceDeptId);
        $targetTableFull = $this->normalizeTableNameWithDepartment($sourceTable, $targetDeptId);
        
        if (!Schema::hasTable($sourceTableFull)) {
            throw new \Exception("Source table {$sourceTableFull} does not exist");
        }
        
        if (Schema::hasTable($targetTableFull)) {
            throw new \Exception("Target table {$targetTableFull} already exists");
        }
        
        // Get columns dari source table
        $columns = Schema::getColumnListing($sourceTableFull);
        
        // Remove system columns
        $excludeColumns = ['id', 'upload_history_id', 'department_id', 'created_at', 'updated_at'];
        $dynamicColumns = array_diff($columns, $excludeColumns);
        
        // Create new table dengan struktur yang sama
        $this->createDynamicTable($sourceTable, $dynamicColumns, $targetDeptId);
        
        return $targetTableFull;
    }
}