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
     * Mapping dianggap cocok jika SEMUA kolom mapping ADA di file Excel
     * Kolom extra di Excel akan diabaikan
     */
    public function findMappingByExcelColumns(int $excelFormatId, array $excelColumns)
    {
        // Normalize excel columns (lowercase, trim)
        $normalizedExcelColumns = array_map(function($col) {
            return strtolower(trim($col));
        }, $excelColumns);

        // Ambil semua mapping untuk format ini
        $mappings = $this->getMappingsByFormat($excelFormatId);

        foreach ($mappings as $mapping) {
            // Ambil keys dari column_mapping (ini adalah nama kolom Excel yang ada di mapping)
            $mappingExcelColumns = array_keys($mapping->column_mapping);
            
            // Normalize mapping columns
            $normalizedMappingColumns = array_map(function($col) {
                return strtolower(trim($col));
            }, $mappingExcelColumns);

            // Cek apakah SEMUA kolom yang ada di mapping, ADA di file Excel
            // Kolom extra di Excel akan diabaikan
            $allMappingColumnsExist = true;
            foreach ($normalizedMappingColumns as $mappingCol) {
                if (!in_array($mappingCol, $normalizedExcelColumns)) {
                    $allMappingColumnsExist = false;
                    break;
                }
            }

            if ($allMappingColumnsExist) {
                return $mapping;
            }
        }

        return null;
    }

    /**
     * Apply mapping ke row data
     * - Kolom yang ada di mapping tetapi tidak ada di row akan di-set null
     * - Kolom yang ada di row tetapi tidak ada di mapping akan diabaikan
     */
    public function applyMapping(array $row, array $columnMapping)
    {
        $mappedData = [];

        // Normalize row keys (header Excel)
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedRow[strtolower(trim($key))] = $value;
        }

        foreach ($columnMapping as $excelColumn => $dbColumn) {
            // Normalize excel column name dari mapping
            $normalizedExcelCol = strtolower(trim($excelColumn));
            
            // Cek apakah kolom ada di row
            // Jika ada, ambil nilainya. Jika tidak, set null
            $mappedData[$dbColumn] = $normalizedRow[$normalizedExcelCol] ?? null;
        }

        // Kolom lain di $row yang tidak ada di $columnMapping akan DIABAIKAN
        return $mappedData;
    }
}