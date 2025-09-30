<?php

namespace App\Services;

use App\Models\MappingConfiguration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MappingService
{
    public function createMapping(
        int $excelFormatId, 
        array $columnMapping, 
        ?array $transformationRules = null,  // ✅ SUDAH BENAR
        ?int $departmentId = null,           // ✅ TAMBAH ? di depan int
        ?string $mappingName = null,         // ✅ TAMBAH ? di depan string
        ?string $description = null          // ✅ TAMBAH ? di depan string
    ) {
        $mappingIndex = 'MAP_' . strtoupper(Str::random(8));

        return MappingConfiguration::create([
            'excel_format_id' => $excelFormatId,
            'mapping_index' => $mappingIndex,
            'mapping_name' => $mappingName,
            'description' => $description,
            'department_id' => $departmentId,
            'column_mapping' => $columnMapping,
            'transformation_rules' => $transformationRules
        ]);
    }

    public function getMappingByIndex(string $mappingIndex)
    {
        return MappingConfiguration::where('mapping_index', $mappingIndex)->firstOrFail();
    }

    public function getMappingsByFormat(int $excelFormatId, ?int $departmentId = null) // ✅ TAMBAH ?
    {
        $query = MappingConfiguration::where('excel_format_id', $excelFormatId);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->get();
    }

    public function getAllMappingsByDepartment(int $departmentId)
    {
        return MappingConfiguration::with('excelFormat')
            ->where('department_id', $departmentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findMappingByExcelColumns(int $excelFormatId, array $excelColumns, ?int $departmentId = null) // ✅ TAMBAH ?
    {
        $normalizedExcelColumns = $this->normalizeColumns($excelColumns);

        Log::info('Finding mapping for columns:', [
            'format_id' => $excelFormatId,
            'excel_columns' => $excelColumns,
            'normalized' => $normalizedExcelColumns,
            'department_id' => $departmentId
        ]);

        $mappings = $this->getMappingsByFormat($excelFormatId, $departmentId);

        foreach ($mappings as $mapping) {
            $mappingExcelColumns = array_keys($mapping->column_mapping);
            $normalizedMappingColumns = $this->normalizeColumns($mappingExcelColumns);

            Log::info('Comparing with mapping:', [
                'mapping_id' => $mapping->id,
                'mapping_index' => $mapping->mapping_index,
                'mapping_columns' => $mappingExcelColumns,
                'normalized' => $normalizedMappingColumns,
                'match' => $normalizedExcelColumns === $normalizedMappingColumns
            ]);

            if ($normalizedExcelColumns === $normalizedMappingColumns) {
                Log::info('Mapping found!', ['mapping_index' => $mapping->mapping_index]);
                return $mapping;
            }
        }

        Log::warning('No matching mapping found');
        return null;
    }

    protected function normalizeColumns(array $columns)
    {
        $normalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $columns);

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