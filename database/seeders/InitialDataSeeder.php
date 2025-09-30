<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Create Departments
        $finance = Department::create([
            'code' => 'FIN',
            'name' => 'Finance Department',
            'description' => 'Department keuangan perusahaan',
            'is_active' => true
        ]);

        $sales = Department::create([
            'code' => 'SALES',
            'name' => 'Sales Department',
            'description' => 'Department penjualan dan marketing',
            'is_active' => true
        ]);

        $hr = Department::create([
            'code' => 'HR',
            'name' => 'Human Resources',
            'description' => 'Department SDM',
            'is_active' => true
        ]);

        // Create Admin Master
        User::create([
            'name' => 'Admin Master',
            'email' => 'admin@uploadcenter.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => null // Admin tidak terikat department
        ]);

        // Create Finance User
        User::create([
            'name' => 'Finance User',
            'email' => 'finance@uploadcenter.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department_id' => $finance->id
        ]);

        // Create Sales User
        User::create([
            'name' => 'Sales User',
            'email' => 'sales@uploadcenter.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department_id' => $sales->id
        ]);

        // Create HR User
        User::create([
            'name' => 'HR User',
            'email' => 'hr@uploadcenter.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department_id' => $hr->id
        ]);

        $this->command->info('✅ Initial data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('-------------------');
        $this->command->info('Admin Master:');
        $this->command->info('  Email: admin@uploadcenter.com');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Finance User:');
        $this->command->info('  Email: finance@uploadcenter.com');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Sales User:');
        $this->command->info('  Email: sales@uploadcenter.com');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('HR User:');
        $this->command->info('  Email: hr@uploadcenter.com');
        $this->command->info('  Password: password');
    }
}