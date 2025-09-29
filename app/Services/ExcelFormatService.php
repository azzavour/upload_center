<?php

namespace App\Services;

use App\Models\ExcelFormat;
use Illuminate\Support\Str;

class ExcelFormatService
{
    public function createFormat(array $data)
    {
        $data['format_code'] = $data['format_code'] ?? Str::slug($data['format_name']);
        
        return ExcelFormat::create($data);
    }

    public function getAllFormats()
    {
        return ExcelFormat::where('is_active', true)->get();
    }

    public function findFormatByCode(string $code)
    {
        return ExcelFormat::where('format_code', $code)->firstOrFail();
    }

    public function findFormatById(int $id)
    {
        return ExcelFormat::findOrFail($id);
    }

    /**
     * Cek apakah file Excel menggunakan format standar (sesuai expected_columns)
     * 
     * @param array $excelColumns
     * @param ExcelFormat $format
     * @return bool
     */
    public function isStandardFormat(array $excelColumns, ExcelFormat $format)
    {
        $expectedColumns = $format->expected_columns;
        
        // Normalize kolom (lowercase dan trim)
        $excelColumnsNormalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $excelColumns);
        
        $expectedColumnsNormalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $expectedColumns);
        
        // Cek apakah kolom Excel sesuai dengan expected columns
        sort($excelColumnsNormalized);
        sort($expectedColumnsNormalized);
        
        // TRUE jika sama (format standar), FALSE jika beda (butuh mapping)
        return $excelColumnsNormalized === $expectedColumnsNormalized;
    }

    /**
     * @deprecated Use isStandardFormat() instead
     * Method lama yang akan dihapus
     */
    public function isNewFormat(array $excelColumns, ExcelFormat $format)
    {
        // Kebalikan dari isStandardFormat
        // TRUE = format baru (butuh mapping)
        // FALSE = format standar
        return !$this->isStandardFormat($excelColumns, $format);
    }
}