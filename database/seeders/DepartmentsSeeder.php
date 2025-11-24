<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsSeeder extends Seeder
{
    public function run()
    {
        // Hanya pertahankan departemen MIS, hapus entri lain yang tidak relevan
        Department::where('code', '!=', 'MIS')->delete();

        Department::updateOrCreate(
            ['code' => 'MIS'],
            [
                'name' => 'MIS',
                'description' => 'Management Information System',
                'is_active' => true
            ]
        );

        $this->command->info('✅ Department MIS seeded successfully!');
    }
}
