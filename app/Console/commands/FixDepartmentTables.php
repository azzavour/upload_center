<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\ExcelFormat;
use App\Services\TableManagerService;

/**
 * Command untuk memperbaiki struktur tabel department
 * 
 * Jalankan: php artisan fix:department-tables
 */
class FixDepartmentTables extends Command
{
    protected $signature = 'fix:department-tables {--dry-run : Show what would be done without making changes}';
    protected $description = 'Fix department table structure and migrate data to prefixed tables';

    protected $tableManager;

    public function __construct(TableManagerService $tableManager)
    {
        parent::__construct();
        $this->tableManager = $tableManager;
    }

    public function handle()
    {
        $this->info('🔧 Starting Department Tables Fix...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // 1. Validasi departments
        $departments = Department::all();
        if ($departments->isEmpty()) {
            $this->error('❌ No departments found. Please create departments first.');
            return 1;
        }

        $this->info('📋 Found ' . $departments->count() . ' departments:');
        foreach ($departments as $dept) {
            $this->line("  - {$dept->code}: {$dept->name}");
        }
        $this->newLine();

        // 2. Process each format
        $formats = ExcelFormat::with('department')->get();
        
        if ($formats->isEmpty()) {
            $this->error('❌ No Excel formats found.');
            return 1;
        }

        $this->info('📄 Found ' . $formats->count() . ' formats to process');
        $this->newLine();

        $stats = [
            'created' => 0,
            'exists' => 0,
            'migrated' => 0,
            'errors' => 0
        ];

        foreach ($formats as $format) {
            $this->processFormat($format, $dryRun, $stats);
        }

        // 3. Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Tables Created', $stats['created']],
                ['Tables Already Exist', $stats['exists']],
                ['Data Migrated', $stats['migrated']],
                ['Errors', $stats['errors']]
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('✅ Dry run completed. Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('✅ Department tables fix completed!');
        }

        return 0;
    }

    protected function processFormat($format, $dryRun, &$stats)
    {
        $this->line("Processing format: {$format->format_name}");

        if (!$format->department) {
            $this->warn("  ⚠️  No department assigned to format {$format->id}");
            $stats['errors']++;
            return;
        }

        $baseTableName = $format->target_table;
        $actualTableName = $this->tableManager->getActualTableName($baseTableName, $format->department_id);

        $this->line("  Base table: {$baseTableName}");
        $this->line("  Expected table: {$actualTableName}");

        // Check if table exists
        $tableExists = $this->tableManager->tableExists($baseTableName, $format->department_id);

        if ($tableExists) {
            $this->line("  ✅ Table already exists: {$actualTableName}");
            $stats['exists']++;
        } else {
            $this->line("  🔨 Creating table: {$actualTableName}");
            
            if (!$dryRun) {
                try {
                    $this->tableManager->createDynamicTable(
                        $baseTableName,
                        $format->expected_columns,
                        $format->department_id
                    );
                    $this->info("  ✅ Table created successfully");
                    $stats['created']++;
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to create table: " . $e->getMessage());
                    $stats['errors']++;
                    return;
                }
            } else {
                $this->line("  [DRY RUN] Would create table with columns:");
                foreach ($format->expected_columns as $col) {
                    $this->line("    - {$col}");
                }
                $stats['created']++;
            }
        }

        // Check for old table without prefix
        $oldTableName = strtolower($baseTableName);
        if (Schema::hasTable($oldTableName) && $oldTableName !== $actualTableName) {
            $this->warn("  🔄 Old table detected: {$oldTableName}");
            
            if (!$dryRun) {
                $this->migrateOldTable($oldTableName, $actualTableName, $stats);
            } else {
                $count = DB::table($oldTableName)->count();
                $this->line("  [DRY RUN] Would migrate {$count} rows");
            }
        }

        $this->newLine();
    }

    protected function migrateOldTable($oldTableName, $newTableName, &$stats)
    {
        try {
            $count = DB::table($oldTableName)->count();
            
            if ($count > 0) {
                $this->line("  📦 Migrating {$count} rows...");
                
                // Get columns from old table
                $oldColumns = Schema::getColumnListing($oldTableName);
                $newColumns = Schema::getColumnListing($newTableName);
                
                // Find matching columns
                $matchingColumns = array_intersect($oldColumns, $newColumns);
                
                if (empty($matchingColumns)) {
                    $this->warn("  ⚠️  No matching columns found. Skipping migration.");
                    return;
                }
                
                $columnList = implode(', ', $matchingColumns);
                
                // Copy data
                DB::statement("INSERT INTO {$newTableName} ({$columnList}) SELECT {$columnList} FROM {$oldTableName}");
                
                $this->info("  ✅ Migrated {$count} rows");
                
                // Rename old table
                $backupName = "{$oldTableName}_backup_" . date('Ymd_His');
                Schema::rename($oldTableName, $backupName);
                $this->info("  ✅ Old table renamed to: {$backupName}");
                
                $stats['migrated']++;
            } else {
                $this->line("  ℹ️  Old table is empty, just renaming...");
                $backupName = "{$oldTableName}_empty_" . date('Ymd_His');
                Schema::rename($oldTableName, $backupName);
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Migration failed: " . $e->getMessage());
            $stats['errors']++;
        }
    }
}