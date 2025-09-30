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
                'code' => 'SPOTIFY',
                'name' => 'Spotify Music',
                'description' => 'Department for Spotify music data management',
                'is_active' => true
            ],
            [
                'code' => 'APPLE_MUSIC',
                'name' => 'Apple Music',
                'description' => 'Department for Apple Music/iTunes data management',
                'is_active' => true
            ],
            [
                'code' => 'YOUTUBE_MUSIC',
                'name' => 'YouTube Music',
                'description' => 'Department for YouTube Music data management',
                'is_active' => true
            ],
            [
                'code' => 'AMAZON_MUSIC',
                'name' => 'Amazon Music',
                'description' => 'Department for Amazon Music data management',
                'is_active' => true
            ],
            [
                'code' => 'DEEZER',
                'name' => 'Deezer',
                'description' => 'Department for Deezer music data management',
                'is_active' => true
            ],
            [
                'code' => 'TIDAL',
                'name' => 'Tidal',
                'description' => 'Department for Tidal music data management',
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