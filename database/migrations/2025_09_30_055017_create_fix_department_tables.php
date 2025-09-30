<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk memastikan semua tabel menggunakan department prefix
 * Jalankan: php artisan migrate
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ambil semua department dan format yang ada
        $departments = DB::table('departments')->get();
        $formats = DB::table('excel_formats')->get();

        foreach ($formats as $format) {
            $department = $departments->firstWhere('id', $format->department_id);
            
            if (!$department) {
                echo "⚠️  Skipping format {$format->id}: No department found\n";
                continue;
            }

            $baseTableName = strtolower(trim($format->target_table));
            $deptCode = strtolower($department->code);
            $expectedTableName = "dept_{$deptCode}_{$baseTableName}";

            // 2. Cek apakah tabel dengan prefix sudah ada
            $tableExists = Schema::hasTable($expectedTableName);
            
            if (!$tableExists) {
                echo "✅ Creating table: {$expectedTableName}\n";
                
                // Parse expected_columns (JSON)
                $expectedColumns = json_decode($format->expected_columns, true);
                
                if (empty($expectedColumns)) {
                    echo "⚠️  Skipping {$expectedTableName}: No columns defined\n";
                    continue;
                }

                // Buat tabel baru
                Schema::create($expectedTableName, function ($table) use ($expectedColumns) {
                    $table->id();
                    $table->foreignId('upload_history_id')->constrained()->onDelete('cascade');
                    $table->foreignId('department_id')->constrained()->onDelete('cascade');
                    
                    // Kolom dinamis
                    foreach ($expectedColumns as $column) {
                        $normalizedColumn = $this->normalizeColumnName($column);
                        $table->string($normalizedColumn, 500)->nullable();
                    }
                    
                    $table->timestamps();
                    $table->index(['department_id', 'upload_history_id']);
                });
                
                echo "✅ Table created: {$expectedTableName}\n";
            } else {
                echo "ℹ️  Table already exists: {$expectedTableName}\n";
            }

            // 3. Cek apakah ada tabel lama tanpa prefix yang perlu di-migrate
            $oldTableName = $baseTableName;
            if (Schema::hasTable($oldTableName) && $oldTableName !== $expectedTableName) {
                echo "🔄 Migrating data from {$oldTableName} to {$expectedTableName}\n";
                
                try {
                    // Copy data dari tabel lama ke tabel baru
                    $count = DB::table($oldTableName)->count();
                    
                    if ($count > 0) {
                        DB::statement("INSERT INTO {$expectedTableName} SELECT * FROM {$oldTableName}");
                        echo "✅ Migrated {$count} rows from {$oldTableName}\n";
                    }
                    
                    // Rename tabel lama untuk backup
                    $backupName = "{$oldTableName}_backup_" . date('Ymd_His');
                    Schema::rename($oldTableName, $backupName);
                    echo "✅ Renamed old table to: {$backupName}\n";
                    
                } catch (\Exception $e) {
                    echo "⚠️  Migration failed: {$e->getMessage()}\n";
                }
            }
        }

        echo "\n✅ Migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada down migration karena ini adalah fix
        // Jangan hapus tabel yang sudah ada
    }

    /**
     * Normalize column name (copy from TableManagerService)
     */
    private function normalizeColumnName(string $columnName): string
    {
        $columnName = strtolower(trim($columnName));
        $columnName = preg_replace('/\s+/', '_', $columnName);
        $columnName = preg_replace('/[^a-z0-9_]/', '', $columnName);
        
        // Hindari reserved keywords
        $reserved = ['id', 'created_at', 'updated_at', 'deleted_at', 'user', 'group', 'order', 'index', 'table'];
        if (in_array($columnName, $reserved)) {
            $columnName = 'col_' . $columnName;
        }
        
        return $columnName;
    }
};