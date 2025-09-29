<?php

namespace App\Services;

use App\Models\MappingConfiguration;
use Illuminate\Support\Str;

class MappingService
{
    public function createMapping(int $excelFormatId, array $columnMapping, ?array $transformationRules = null)
    {
        $mappingIndex = 'MAP_' . strtoupper(Str::random(8));

        return MappingConfiguration::create([
            'excel_format_id' => $excelFormatId,
            'mapping_index' => $mappingIndex,
            'column_mapping' => $columnMapping,
            'transformation_rules' => $transformationRules
        ]);
    }

    public function getMappingByIndex(string $mappingIndex)
    {
        return MappingConfiguration::where('mapping_index', $mappingIndex)->firstOrFail();
    }

    public function getMappingsByFormat(int $excelFormatId)
    {
        return MappingConfiguration::where('excel_format_id', $excelFormatId)->get();
    }

    /**
     * Cari mapping yang cocok berdasarkan kolom Excel yang diupload
     * 
     * @param int $excelFormatId
     * @param array $excelColumns Header kolom dari file Excel yang diupload
     * @return MappingConfiguration|null
     */
    public function findMappingByExcelColumns(int $excelFormatId, array $excelColumns)
    {
        // Normalize excel columns (lowercase, trim, sort)
        $normalizedExcelColumns = $this->normalizeColumns($excelColumns);

        // Ambil semua mapping untuk format ini
        $mappings = $this->getMappingsByFormat($excelFormatId);

        foreach ($mappings as $mapping) {
            // Ambil keys dari column_mapping (ini adalah nama kolom Excel yang sudah dimapping)
            $mappingExcelColumns = array_keys($mapping->column_mapping);
            
            // Normalize mapping columns
            $normalizedMappingColumns = $this->normalizeColumns($mappingExcelColumns);

            // Bandingkan apakah struktur kolomnya sama
            if ($normalizedExcelColumns === $normalizedMappingColumns) {
                return $mapping;
            }
        }

        return null;
    }

    /**
     * Normalize kolom untuk perbandingan
     * - Lowercase semua
     * - Trim whitespace
     * - Sort alphabetically
     * 
     * @param array $columns
     * @return array
     */
    protected function normalizeColumns(array $columns)
    {
        // Lowercase dan trim
        $normalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $columns);

        // Sort
        sort($normalized);

        return $normalized;
    }

    public function applyMapping(array $row, array $columnMapping)
    {
        $mappedData = [];

        foreach ($columnMapping as $excelColumn => $dbColumn) {
            $mappedData[$dbColumn] = $row[$excelColumn] ?? null;
        }

        return $mappedData;
    }
}