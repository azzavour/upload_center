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

    public function applyMapping(array $row, array $columnMapping)
    {
        $mappedData = [];

        foreach ($columnMapping as $excelColumn => $dbColumn) {
            $mappedData[$dbColumn] = $row[$excelColumn] ?? null;
        }

        return $mappedData;
    }
}