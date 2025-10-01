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

            // ✅ PARTIAL MATCHING: Cek apakah semua kolom mapping ada di Excel
            $mappingColumnsExistInExcel = empty(array_diff($normalizedMappingColumns, $normalizedExcelColumns));
            
            // Hitung persentase match
            $matchCount = count(array_intersect($normalizedMappingColumns, $normalizedExcelColumns));
            $totalMappingColumns = count($normalizedMappingColumns);
            $matchPercentage = $totalMappingColumns > 0 ? ($matchCount / $totalMappingColumns) * 100 : 0;

            Log::info('Comparing with mapping:', [
                'mapping_id' => $mapping->id,
                'mapping_index' => $mapping->mapping_index,
                'mapping_columns' => $mappingExcelColumns,
                'normalized_mapping' => $normalizedMappingColumns,
                'normalized_excel' => $normalizedExcelColumns,
                'match_count' => $matchCount,
                'match_percentage' => $matchPercentage,
                'all_mapping_columns_exist' => $mappingColumnsExistInExcel
            ]);

            // ✅ Gunakan mapping jika semua kolom mapping ada di Excel (minimal 100% dari mapping)
            if ($mappingColumnsExistInExcel) {
                Log::info('Mapping found! (Partial match)', [
                    'mapping_index' => $mapping->mapping_index,
                    'match_percentage' => $matchPercentage
                ]);
                return $mapping;
            }
        }

        Log::warning('No matching mapping found');
        return null;
    }

    protected function normalizeColumns(array $columns)
    {
        $normalized = array_map(function($col) {
            // Trim whitespace
            $col = trim($col);
            // Convert to lowercase
            $col = strtolower($col);
            // Replace spaces with underscore
            $col = preg_replace('/\s+/', '_', $col);
            // Remove invalid characters (keep only a-z, 0-9, _)
            $col = preg_replace('/[^a-z0-9_]/', '', $col);
            return $col;
        }, $columns);

        sort($normalized);

        return $normalized;
    }

    public function applyMapping(array $row, array $columnMapping)
    {
        $mappedData = [];

        // Normalize row keys untuk matching
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeColumn($key);
            $normalizedRow[$normalizedKey] = $value;
        }

        foreach ($columnMapping as $excelColumn => $dbColumn) {
            // Normalize excel column dari mapping
            $normalizedExcelCol = $this->normalizeColumn($excelColumn);
            
            // Cari value dari row dengan normalized key
            $mappedData[$dbColumn] = $normalizedRow[$normalizedExcelCol] ?? null;
        }

        return $mappedData;
    }

    /**
     * ✅ BARU: Deteksi kolom yang diabaikan
     */
    public function getIgnoredColumns(array $excelColumns, array $columnMapping): array
    {
        $normalizedExcelColumns = $this->normalizeColumns($excelColumns);
        $normalizedMappingColumns = $this->normalizeColumns(array_keys($columnMapping));
        
        // Kolom yang ada di Excel tapi tidak ada di mapping
        $ignoredColumns = array_diff($normalizedExcelColumns, $normalizedMappingColumns);
        
        // Return original column names (not normalized)
        $originalIgnoredColumns = [];
        foreach ($excelColumns as $col) {
            $normalized = $this->normalizeColumn($col);
            if (in_array($normalized, $ignoredColumns)) {
                $originalIgnoredColumns[] = $col;
            }
        }
        
        return $originalIgnoredColumns;
    }

    /**
     * ✅ BARU: Normalize single column
     */
    protected function normalizeColumn(string $column): string
    {
        $col = trim($column);
        $col = strtolower($col);
        $col = preg_replace('/\s+/', '_', $col);
        $col = preg_replace('/[^a-z0-9_]/', '', $col);
        return $col;
    }
}