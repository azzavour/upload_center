<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\User;
use App\Models\ExcelFormat;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Quick setup untuk inisialisasi sistem multi-department
 * 
 * Jalankan: php artisan setup:multi-department
 */
class QuickSetupMultiDepartment extends Command
{
    protected $signature = 'setup:multi-department {--reset : Reset all data and start fresh}';
    protected $description = 'Quick setup for multi-department system';

    public function handle()
    {
        $this->info('🚀 Multi-Department System Setup');
        $this->newLine();

        if ($this->option('reset')) {
            if (!$this->confirm('⚠️  This will DELETE ALL existing data. Continue?')) {
                $this->error('Setup cancelled.');
                return 1;
            }
            $this->resetDatabase();
        }

        // Step 1: Setup Departments
        $this->info('1️⃣  Setting up Departments...');
        $departments = $this->setupDepartments();
        $this->newLine();

        // Step 2: Setup Admin User
        $this->info('2️⃣  Setting up Admin User...');
        $admin = $this->setupAdmin();
        $this->newLine();

        // Step 3: Setup Department Users
        $this->info('3️⃣  Setting up Department Users...');
        $users = $this->setupUsers($departments);
        $this->newLine();

        // Step 4: Setup Excel Formats
        $this->info('4️⃣  Setting up Excel Formats...');
        $formats = $this->setupFormats($departments);
        $this->newLine();

        // Step 5: Summary
        $this->displaySummary($departments, $admin, $users, $formats);

        $this->newLine();
        $this->info('✅ Setup completed successfully!');
        $this->newLine();
        
        $this->info('🎯 Next steps:');
        $this->line('   1. Run: php artisan test:department-setup');
        $this->line('   2. Login dengan salah satu user di atas');
        $this->line('   3. Test upload file Excel');
        
        return 0;
    }

    protected function resetDatabase()
    {
        $this->warn('Resetting database...');
        
        \DB::table('master_data')->delete();
        \DB::table('upload_histories')->delete();
        \DB::table('mapping_configurations')->delete();
        \DB::table('excel_formats')->delete();
        \DB::table('users')->delete();
        \DB::table('departments')->delete();
        
        $this->info('✅ Database reset complete');
        $this->newLine();
    }

    protected function setupDepartments()
    {
        $departments = [];
        
        $defaultDepartments = [
            ['code' => 'hr', 'name' => 'Human Resources', 'description' => 'HR Department'],
            ['code' => 'fin', 'name' => 'Finance', 'description' => 'Finance Department'],
            ['code' => 'it', 'name' => 'Information Technology', 'description' => 'IT Department'],
            ['code' => 'ops', 'name' => 'Operations', 'description' => 'Operations Department'],
        ];

        foreach ($defaultDepartments as $deptData) {
            $dept = Department::firstOrCreate(
                ['code' => $deptData['code']],
                [
                    'name' => $deptData['name'],
                    'description' => $deptData['description'],
                    'is_active' => true
                ]
            );
            
            $departments[] = $dept;
            $this->line("   ✅ {$dept->code}: {$dept->name}");
        }

        return collect($departments);
    }

    protected function setupAdmin()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'Admin Master',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'department_id' => null
            ]
        );

        $this->line("   ✅ Admin created");
        $this->line("      Email: admin@company.com");
        $this->line("      Password: password123");
        
        return $admin;
    }

    protected function setupUsers($departments)
    {
        $users = [];

        foreach ($departments as $dept) {
            $email = strtolower($dept->code) . '@company.com';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $dept->name . ' User',
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'department_id' => $dept->id
                ]
            );
            
            $users[] = $user;
            $this->line("   ✅ {$user->name}");
            $this->line("      Email: {$email}");
            $this->line("      Department: {$dept->code}");
        }

        $this->line("      Password (all): password123");
        
        return collect($users);
    }

    protected function setupFormats($departments)
    {
        $formats = [];

        // Format standar untuk semua department
        $standardColumns = [
            'nama_produk',
            'kategori',
            'harga',
            'stok',
            'satuan',
            'keterangan'
        ];

        foreach ($departments as $dept) {
            $format = ExcelFormat::firstOrCreate(
                [
                    'format_code' => 'produk_' . $dept->code,
                    'department_id' => $dept->id
                ],
                [
                    'format_name' => 'Format Produk ' . $dept->name,
                    'description' => 'Format standar untuk data produk department ' . $dept->name,
                    'expected_columns' => $standardColumns,
                    'target_table' => 'produk',
                    'is_active' => true
                ]
            );
            
            $formats[] = $format;
            $this->line("   ✅ {$format->format_name}");
            $this->line("      Target: dept_{$dept->code}_produk");
        }

        return collect($formats);
    }

    protected function displaySummary($departments, $admin, $users, $formats)
    {
        $this->info('📊 Setup Summary');
        $this->newLine();

        // Departments Table
        $this->table(
            ['Code', 'Name', 'Status'],
            $departments->map(fn($d) => [
                $d->code,
                $d->name,
                $d->is_active ? '✅ Active' : '❌ Inactive'
            ])
        );

        // Users Table
        $this->newLine();
        $this->info('👥 Users Created:');
        $this->table(
            ['Email', 'Role', 'Department'],
            collect([$admin])->merge($users)->map(fn($u) => [
                $u->email,
                $u->role === 'admin' ? '🔴 Admin' : '🔵 User',
                $u->department ? $u->department->code : 'N/A'
            ])
        );

        // Formats Table
        $this->newLine();
        $this->info('📄 Excel Formats:');
        $this->table(
            ['Format Name', 'Department', 'Target Table'],
            $formats->map(fn($f) => [
                $f->format_name,
                $f->department->code,
                "dept_{$f->department->code}_{$f->target_table}"
            ])
        );
    }
}