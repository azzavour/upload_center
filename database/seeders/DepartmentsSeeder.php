<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsSeeder extends Seeder
{
    /**
     * Seed music streaming service departments
     */
    public function run()
    {
        $musicDepartments = [
            [
                'code' => 'MIS',
                'name' => 'MIS',
                'description' => 'Management Information System',
                'is_active' => true
            ],
        ];

        foreach ($musicDepartments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }

        $this->command->info('✅ Music streaming departments seeded successfully!');
    }
}
