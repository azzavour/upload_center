<?php

namespace App\Services;

use App\Models\ExcelFormat;
use Illuminate\Support\Str;

class ExcelFormatService
{
    protected $tableManager;

    public function __construct(TableManagerService $tableManager)
    {
        $this->tableManager = $tableManager;
    }

    public function createFormat(array $data, int $departmentId)
    {
        $data['format_code'] = $data['format_code'] ?? Str::slug($data['format_name']);
        $data['department_id'] = $departmentId;
        
        // Normalisasi target table
        $data['target_table'] = $this->tableManager->normalizeTableName($data['target_table']);
        
        // Normalisasi expected columns
        if (isset($data['expected_columns']) && is_array($data['expected_columns'])) {
            $data['expected_columns'] = array_map(
                fn($col) => $this->tableManager->normalizeColumnName($col),
                $data['expected_columns']
            );
        }
        
        // Buat tabel jika belum ada
        if (!$this->tableManager->tableExists($data['target_table'])) {
            $this->tableManager->createDynamicTable(
                $data['target_table'],
                $data['expected_columns']
            );
        }
        
        return ExcelFormat::create($data);
    }

    public function getAllFormats(int $departmentId = null)
    {
        $query = ExcelFormat::where('is_active', true);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->get();
    }

    public function findFormatByCode(string $code, int $departmentId = null)
    {
        $query = ExcelFormat::where('format_code', $code);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->firstOrFail();
    }

    public function findFormatById(int $id)
    {
        return ExcelFormat::findOrFail($id);
    }

    public function isStandardFormat(array $excelColumns, ExcelFormat $format)
    {
        $expectedColumns = $format->expected_columns;
        
        $excelColumnsNormalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $excelColumns);
        
        $expectedColumnsNormalized = array_map(function($col) {
            return strtolower(trim($col));
        }, $expectedColumns);
        
        sort($excelColumnsNormalized);
        sort($expectedColumnsNormalized);
        
        return $excelColumnsNormalized === $expectedColumnsNormalized;
    }

    public function isNewFormat(array $excelColumns, ExcelFormat $format)
    {
        return !$this->isStandardFormat($excelColumns, $format);
    }
}