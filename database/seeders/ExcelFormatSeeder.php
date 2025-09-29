<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcelFormat;

class ExcelFormatSeeder extends Seeder
{
    public function run()
    {
        ExcelFormat::create([
            'format_name' => 'Music Tracks Format Standard',
            'format_code' => 'music-tracks-v1',
            'description' => 'Format standar untuk import data musik/lagu dari iTunes atau Spotify',
            'expected_columns' => [
                'Track ID',
                'Track Name',
                'Artist ID',
                'Artist Name',
                'Album Name',
                'Genre',
                'Release Date',
                'Track Price',
                'Collection Price',
                'Country'
            ],
            'target_table' => 'tracks',
            'is_active' => true
        ]);

        // Format alternatif dengan nama kolom Indonesia
        ExcelFormat::create([
            'format_name' => 'Format Musik Indonesia',
            'format_code' => 'music-tracks-indo',
            'description' => 'Format alternatif dengan nama kolom dalam Bahasa Indonesia',
            'expected_columns' => [
                'ID Track',
                'Nama Lagu',
                'ID Artis',
                'Nama Artis',
                'Nama Album',
                'Genre Musik',
                'Tanggal Rilis',
                'Harga Track',
                'Harga Collection',
                'Negara'
            ],
            'target_table' => 'tracks',
            'is_active' => true
        ]);
    }
}