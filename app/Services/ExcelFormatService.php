<?php

namespace App\Services;

use App\Models\ExcelFormat;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        
        // Normalisasi target table (base name tanpa prefix)
        $data['target_table'] = $this->tableManager->normalizeTableName($data['target_table']);
        
        // Normalisasi expected columns
        if (isset($data['expected_columns']) && is_array($data['expected_columns'])) {
            $data['expected_columns'] = array_map(
                fn($col) => $this->tableManager->normalizeColumnName($col),
                $data['expected_columns']
            );
        }
        
        // ✅ PERBAIKAN: Buat tabel dengan prefix department
        $actualTableName = $this->tableManager->getActualTableName($data['target_table'], $departmentId);
        
        Log::info('Creating format with table', [
            'format_name' => $data['format_name'],
            'base_table' => $data['target_table'],
            'actual_table' => $actualTableName,
            'department_id' => $departmentId,
            'expected_columns' => $data['expected_columns']
        ]);
        
        // Cek apakah tabel sudah ada
        if (!$this->tableManager->tableExists($data['target_table'], $departmentId)) {
            Log::info('Table does not exist, creating new table', [
                'table' => $actualTableName
            ]);
            
            $this->tableManager->createDynamicTable(
                $data['target_table'],
                $data['expected_columns'],
                $departmentId
            );
            
            Log::info('Table created successfully', [
                'table' => $actualTableName
            ]);
        } else {
            Log::info('Table already exists', [
                'table' => $actualTableName
            ]);
        }
        
        return ExcelFormat::create($data);
    }

    public function getAllFormats(?int $departmentId = null)
    {
        $query = ExcelFormat::where('is_active', true);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->orderBy('format_name')->get();
    }

    public function findFormatByCode(string $code, ?int $departmentId = null)
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

    /**
     * Get actual table name dengan department prefix
     */
    public function getActualTableName(ExcelFormat $format): string
    {
        return $this->tableManager->getActualTableName(
            $format->target_table,
            $format->department_id
        );
    }

    /**
     * ✅ Check if Excel columns match expected format
     */
    public function isStandardFormat(array $excelColumns, ExcelFormat $format): bool
    {
        $expectedColumns = $format->expected_columns;
        
        // Normalize untuk comparison
        $excelColumnsNormalized = array_map(function($col) {
            return $this->tableManager->normalizeColumnName($col);
        }, $excelColumns);
        
        $expectedColumnsNormalized = array_map(function($col) {
            return $this->tableManager->normalizeColumnName($col);
        }, $expectedColumns);
        
        sort($excelColumnsNormalized);
        sort($expectedColumnsNormalized);
        
        return $excelColumnsNormalized === $expectedColumnsNormalized;
    }

    public function isNewFormat(array $excelColumns, ExcelFormat $format): bool
    {
        return !$this->isStandardFormat($excelColumns, $format);
    }

    /**
     * ✅ BARU: Get format dengan validasi department access
     */
    public function getFormatWithAccessCheck(int $formatId, ?int $userDepartmentId = null, bool $isAdmin = false): ExcelFormat
    {
        $format = $this->findFormatById($formatId);
        
        // Admin bisa akses semua format
        if ($isAdmin) {
            return $format;
        }
        
        // User hanya bisa akses format dari department sendiri
        if (!$userDepartmentId || $format->department_id !== $userDepartmentId) {
            throw new \Exception('Unauthorized access to this format');
        }
        
        return $format;
    }
}