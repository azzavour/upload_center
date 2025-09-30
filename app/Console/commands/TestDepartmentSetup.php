<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\ExcelFormat;
use App\Models\User;
use App\Services\TableManagerService;

/**
 * Command untuk testing department setup
 * 
 * Jalankan: php artisan test:department-setup
 */
class TestDepartmentSetup extends Command
{
    protected $signature = 'test:department-setup';
    protected $description = 'Test department setup and table structure';

    protected $tableManager;

    public function __construct(TableManagerService $tableManager)
    {
        parent::__construct();
        $this->tableManager = $tableManager;
    }

    public function handle()
    {
        $this->info('🧪 Testing Department Setup...');
        $this->newLine();

        $allPassed = true;

        // Test 1: Check Departments
        $this->info('1️⃣  Testing Departments...');
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->error('   ❌ FAILED: No departments found');
            $allPassed = false;
        } else {
            $this->info("   ✅ PASSED: Found {$departments->count()} departments");
            foreach ($departments as $dept) {
                $this->line("      • ID: {$dept->id}, Code: {$dept->code}, Name: {$dept->name}");
            }
        }
        $this->newLine();

        // Test 2: Check Users
        $this->info('2️⃣  Testing Users...');
        $users = User::with('department')->get();
        
        $usersWithDept = $users->filter(fn($u) => $u->department_id !== null);
        $usersWithoutDept = $users->filter(fn($u) => $u->department_id === null);
        
        $this->info("   ✅ Total users: {$users->count()}");
        $this->info("   ✅ Users with department: {$usersWithDept->count()}");
        
        if ($usersWithoutDept->count() > 0) {
            $this->warn("   ⚠️  Users without department: {$usersWithoutDept->count()}");
            foreach ($usersWithoutDept as $user) {
                $this->line("      • {$user->email}");
            }
        }
        $this->newLine();

        // Test 3: Check Excel Formats
        $this->info('3️⃣  Testing Excel Formats...');
        $formats = ExcelFormat::with('department')->get();
        
        if ($formats->isEmpty()) {
            $this->warn('   ⚠️  No Excel formats found');
        } else {
            $this->info("   ✅ Found {$formats->count()} formats");
            
            foreach ($formats as $format) {
                // ✅ SKIP format tanpa department_id
                if (!$format->department_id) {
                    $this->warn("      ⚠️  Format: {$format->format_name}");
                    $this->warn("         Missing department_id! Please assign this format to a department.");
                    $allPassed = false;
                    continue;
                }
                
                $baseTable = $format->target_table;
                $actualTable = $this->tableManager->getActualTableName($baseTable, $format->department_id);
                $exists = Schema::hasTable($actualTable);
                
                $status = $exists ? '✅' : '❌';
                $this->line("      {$status} Format: {$format->format_name}");
                $this->line("         Base: {$baseTable} → Actual: {$actualTable}");
                $this->line("         Department: {$format->department->code}");
                
                if (!$exists) {
                    $this->error("         ❌ Table does not exist!");
                    $allPassed = false;
                }
            }
        }
        $this->newLine();

        // Test 4: Check Department Tables
        $this->info('4️⃣  Testing Department-Specific Tables...');
        
        foreach ($departments as $dept) {
            $this->line("   Department: {$dept->code}");
            $tables = $this->tableManager->getAllDepartmentTablesInDB($dept->id);
            
            if (empty($tables)) {
                $this->warn("      ⚠️  No tables found for this department");
            } else {
                 $tableCount = count($tables);
   $this->info("      ✅ Found {$tableCount} tables:");
                foreach ($tables as $table) {
                    $rowCount = DB::table($table)->count();
                    $this->line("         • {$table} ({$rowCount} rows)");
                }
            }
        }
        $this->newLine();

        // Test 5: Check Upload History
        $this->info('5️⃣  Testing Upload History...');
        $uploadCount = DB::table('upload_histories')->count();
        $this->info("   ✅ Found {$uploadCount} upload records");
        
        if ($uploadCount > 0) {
            $this->line('   Recent uploads:');
            $recentUploads = DB::table('upload_histories')
                ->join('users', 'upload_histories.uploaded_by', '=', 'users.id')
                ->join('departments', 'upload_histories.department_id', '=', 'departments.id')
                ->select('upload_histories.*', 'users.name as user_name', 'departments.code as dept_code')
                ->orderBy('uploaded_at', 'desc')
                ->limit(5)
                ->get();
            
            foreach ($recentUploads as $upload) {
                $this->line("      • {$upload->original_filename} by {$upload->user_name} ({$upload->dept_code}) - {$upload->status}");
            }
        }
        $this->newLine();

        // Test 6: Check Master Data
        $this->info('6️⃣  Testing Master Data...');
        $masterCount = DB::table('master_data')->count();
        $this->info("   ✅ Found {$masterCount} master data records");
        
        if ($masterCount > 0) {
            $byDept = DB::table('master_data')
                ->join('departments', 'master_data.department_id', '=', 'departments.id')
                ->select('departments.code', DB::raw('count(*) as count'))
                ->groupBy('departments.code')
                ->get();
            
            $this->line('   Records by department:');
            foreach ($byDept as $row) {
                $this->line("      • {$row->code}: {$row->count} records");
            }
        }
        $this->newLine();

        // Test 7: Validate Table Structure
        $this->info('7️⃣  Validating Table Structures...');
        
        $requiredColumns = ['id', 'upload_history_id', 'department_id', 'created_at', 'updated_at'];
        
        foreach ($formats as $format) {
            // Skip jika tidak ada department_id
            if (!$format->department_id) {
                continue;
            }
            
            $actualTable = $this->tableManager->getActualTableName($format->target_table, $format->department_id);
            
            if (!Schema::hasTable($actualTable)) {
                continue;
            }
            
            $columns = Schema::getColumnListing($actualTable);
            $missingColumns = array_diff($requiredColumns, $columns);
            
            if (empty($missingColumns)) {
                $this->info("   ✅ {$actualTable}: All required columns present");
            } else {
                $this->error("   ❌ {$actualTable}: Missing columns: " . implode(', ', $missingColumns));
                $allPassed = false;
            }
        }
        $this->newLine();

        // Final Summary
        if ($allPassed) {
            $this->info('✅ All tests PASSED! Department setup is correct.');
        } else {
            $this->error('❌ Some tests FAILED! Please fix the issues above.');
            $this->newLine();
            $this->info('💡 Suggested actions:');
            $this->line('   1. Run: php artisan fix:department-tables');
            $this->line('   2. Ensure all users have department_id set');
            $this->line('   3. Ensure all formats have department_id set');
        }

        return $allPassed ? 0 : 1;
    }
}