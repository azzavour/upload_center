<?php

namespace App\Services;

use App\Models\UploadHistory;
use App\Models\ExcelFormat;
use App\Models\MappingConfiguration;
use App\Models\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UploadService
{
    protected $mappingService;
    protected $masterDataService;
    protected $excelFormatService;
    protected $tableManager;

    public function __construct(
        MappingService $mappingService,
        MasterDataService $masterDataService,
        ExcelFormatService $excelFormatService,
        TableManagerService $tableManager
    ) {
        $this->mappingService = $mappingService;
        $this->masterDataService = $masterDataService;
        $this->excelFormatService = $excelFormatService;
        $this->tableManager = $tableManager;
    }

    public function processUpload(
        $file,
        ExcelFormat $format,
        ?MappingConfiguration $mapping = null,
        ?int $departmentId = null,
        ?int $userId = null,
        string $uploadMode = 'append'
    ) {
        if (!$departmentId) {
            throw new \Exception('Department ID is required for upload');
        }

        $originalFilename = $file->getClientOriginalName();
        $storedPath = $file->store('uploads/sellout');

        $history = UploadHistory::create([
            'excel_format_id' => $format->id,
            'mapping_configuration_id' => $mapping?->id,
            'department_id' => $departmentId,
            'uploaded_by' => $userId,
            'original_filename' => $originalFilename,
            'stored_filename' => basename($storedPath),
            'status' => 'pending',
            'upload_mode' => $uploadMode,
            'uploaded_at' => now(),
            'total_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
        ]);

        try {
            $history->status = 'processing';
            $history->save();

            $this->processStoredFile(
                $history,
                $storedPath,
                $format,
                $mapping,
                $departmentId,
                $userId,
                $uploadMode
            );

            $history->refresh();
            $history->status = 'completed';
            $history->save();
        } catch (\Throwable $e) {
            $history->status = 'failed';
            $history->error_details = ['message' => $e->getMessage()];
            $history->save();
            throw $e;
        }

        return $history;
    }

    /**
     * Proses file yang sudah disimpan di storage (dipakai oleh job).
     */
    public function processStoredFile(
        UploadHistory $history,
        string $storedPath,
        ExcelFormat $format,
        ?MappingConfiguration $mapping = null,
        ?int $departmentId = null,
        ?int $userId = null,
        string $uploadMode = 'append'
    ): int {
        if (!$departmentId) {
            throw new \Exception('Department ID is required for upload');
        }

        $storedFilename = basename($storedPath);
        if (empty($history->stored_filename)) {
            $history->stored_filename = $storedFilename;
            $history->save();
        }

        $actualTableName = $this->ensureDepartmentTableExists($format, $departmentId);

        if ($uploadMode === 'replace') {
            Log::info('Replace mode: Deleting previous data', [
                'table' => $actualTableName,
                'department_id' => $departmentId
            ]);

            DB::table($actualTableName)
                ->where('department_id', $departmentId)
                ->delete();
        }

        Log::info('Starting upload process', [
            'history_id' => $history->id,
            'base_table' => $format->target_table,
            'actual_table' => $actualTableName,
            'department_id' => $departmentId,
            'upload_mode' => $uploadMode
        ]);

        $rowsInserted = $this->importData($storedPath, $format, $mapping, $history, $departmentId, $actualTableName);

        FileUpload::create([
            'upload_history_id' => $history->id,
            'department_id' => $departmentId,
            'uploaded_by' => $userId,
            'original_filename' => $history->original_filename,
            'stored_filename' => $storedFilename,
            'target_table' => $actualTableName,
            'format_name' => $format->format_name,
            'rows_inserted' => $rowsInserted,
            'upload_mode' => $uploadMode,
            'uploaded_at' => now()
        ]);

        $history->loadMissing('excelFormat');

        if (config('upload.sync_master_data', true)) {
            $this->masterDataService->syncToMasterData($history);
        } else {
            Log::info('Master data sync skipped (disabled via config)', [
                'history_id' => $history->id,
            ]);
        }

        Log::info('Upload completed successfully', [
            'history_id' => $history->id,
            'table' => $actualTableName
        ]);

        return $rowsInserted;
    }

    protected function ensureDepartmentTableExists(ExcelFormat $format, int $departmentId): string
    {
        $actualTableName = $this->tableManager->getActualTableName($format->target_table, $departmentId);

        if (!$this->tableManager->tableExists($format->target_table, $departmentId)) {
            Log::info('Creating department table', [
                'base_table' => $format->target_table,
                'actual_table' => $actualTableName,
                'department_id' => $departmentId
            ]);

            $this->tableManager->createDynamicTable(
                $format->target_table,
                $format->expected_columns,
                $departmentId
            );
        }

        return $actualTableName;
    }

    protected function importData(
        $path,
        ExcelFormat $format,
        ?MappingConfiguration $mapping,
        UploadHistory $history,
        ?int $departmentId,
        string $actualTableName
    ) {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (!Storage::exists($path)) {
            throw new \Exception('File ' . $path . ' tidak ditemukan di dalam storage.');
        }

        $fullPath = Storage::path($path);

        $validColumns = $this->getTableColumns($actualTableName);
        if (empty($validColumns)) {
            throw new \Exception("Tabel {$actualTableName} tidak ditemukan atau tidak memiliki kolom.");
        }

        $validColumnFlipped = array_flip($validColumns);
        $mappingColumns = $mapping?->column_mapping ?? [];
        $transformationRules = $mapping?->transformation_rules ?? [];

        $transformTrack = function (array $data) {
            return $this->transformTrackData($data);
        };
        $applyTransformations = function (array $data, ?array $rules = null) {
            return $rules ? $this->applyTransformations($data, $rules) : $data;
        };

        $maxParams = 2000; // jaga di bawah limit 2100
        $columnsCount = max(1, count($validColumnFlipped));
        $maxRowsPerBatch = max(1, intdiv($maxParams, $columnsCount));

        $insertCallback = function (string $table, array $rows) {
            return $this->insertChunked($table, $rows);
        };

        $progressCallback = function (int $successCount, int $failedCount) use ($history) {
            $history->success_rows = $successCount;
            $history->failed_rows = $failedCount;
            $history->total_rows = $successCount + $failedCount;
            $history->save();
        };

        $import = new class (
            $actualTableName,
            $history,
            $departmentId,
            $mappingColumns,
            $transformationRules,
            $validColumnFlipped,
            $this->mappingService,
            $transformTrack,
            $applyTransformations,
            $insertCallback,
            $progressCallback,
            $maxRowsPerBatch
        ) implements OnEachRow, WithHeadingRow, WithChunkReading {
            public int $successCount = 0;
            public int $failedCount = 0;
            public array $errors = [];
            private array $buffer = [];

            public function __construct(
                private string $table,
                private UploadHistory $history,
                private int $departmentId,
                private array $mappingColumns,
                private array $transformationRules,
                private array $validColumnFlipped,
                private MappingService $mappingService,
                private \Closure $transformTrack,
                private \Closure $applyTransformations,
                private $insertCallback,
                private $progressCallback,
                private int $batchSize
            ) {
            }

            public function onRow(Row $row)
            {
                // Hormati pembatalan manual: jika status sudah bukan pending/processing, hentikan
                $this->history->refresh();
                if (!in_array($this->history->status, ['pending', 'processing'])) {
                    $this->buffer = [];
                    return;
                }

                $rowArray = $row->toArray();

                $nonEmpty = array_filter($rowArray, fn($v) => $v !== null && $v !== '');
                if (empty($nonEmpty)) {
                    return;
                }

                try {
                    if (!empty($this->mappingColumns)) {
                        $rowArray = $this->mappingService->applyMapping($rowArray, $this->mappingColumns);
                    }

                    if (!empty($this->transformationRules)) {
                        $rowArray = ($this->applyTransformations)($rowArray, $this->transformationRules);
                    }

                    $rowArray = ($this->transformTrack)($rowArray);

                    $rowArray['upload_history_id'] = $this->history->id;
                    $rowArray['department_id'] = $this->departmentId;
                    $rowArray['created_at'] = now();
                    $rowArray['updated_at'] = now();

                    $filtered = array_intersect_key($rowArray, $this->validColumnFlipped);

                    if (empty($filtered)) {
                        throw new \Exception('Tidak ada kolom valid untuk di-insert');
                    }

                    $this->buffer[] = $filtered;

                    if (count($this->buffer) >= $this->batchSize) {
                        $this->flush();
                    }

                    $this->successCount++;
                } catch (\Throwable $e) {
                    $this->failedCount++;
                    if (count($this->errors) < 100) {
                        $this->errors[] = [
                            'row' => $row->getIndex(),
                            'error' => $e->getMessage()
                        ];
                    }
                    Log::warning('Row import failed', [
                        'table' => $this->table,
                        'row_index' => $row->getIndex(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            public function chunkSize(): int
            {
                return 500;
            }

            public function flush(): void
            {
                if (empty($this->buffer)) {
                    return;
                }

                // Jangan insert lagi jika status sudah dibatalkan/failed
                $this->history->refresh();
                if (!in_array($this->history->status, ['pending', 'processing'])) {
                    $this->buffer = [];
                    return;
                }

                call_user_func($this->insertCallback, $this->table, $this->buffer);
                $this->buffer = [];

                call_user_func($this->progressCallback, $this->successCount, $this->failedCount);
            }
        };

        Excel::import($import, $fullPath);

        // Flush sisa buffer jika method tersedia (anonymous class di atas mendefinisikan flush)
        if (method_exists($import, 'flush')) {
            call_user_func([$import, 'flush']);
        }

        $totalProcessed = $import->successCount + $import->failedCount;

        $history->error_details = $import->errors;
        $history->total_rows = $totalProcessed;
        $history->success_rows = $import->successCount;
        $history->failed_rows = $import->failedCount;
        $history->save();

        Log::info('Import completed (chunked)', [
            'table' => $actualTableName,
            'total_rows' => $totalProcessed,
            'success' => $import->successCount,
            'failed' => $import->failedCount
        ]);

        return $import->successCount;
    }

    protected function getTableColumns(string $tableName): array
    {
        $lowerTableName = strtolower($tableName);

        try {
            $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ?", [$lowerTableName]);
            return collect($columns)->pluck('column_name')->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get table columns', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function transformTrackData(array $data)
    {
        if (isset($data['release_date']) && !empty($data['release_date'])) {
            try {
                if (is_numeric($data['release_date'])) {
                    $data['release_date'] = Carbon::createFromFormat('Y-m-d', '1900-01-01')
                        ->addDays($data['release_date'] - 2)
                        ->format('Y-m-d');
                } else {
                    $data['release_date'] = Carbon::parse($data['release_date'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $data['release_date'] = null;
            }
        }

        if (isset($data['track_price'])) {
            $data['track_price'] = preg_replace('/[^0-9.]/', '', $data['track_price']);
            $data['track_price'] = $data['track_price'] ?: null;
        }

        if (isset($data['collection_price'])) {
            $data['collection_price'] = preg_replace('/[^0-9.]/', '', $data['collection_price']);
            $data['collection_price'] = $data['collection_price'] ?: null;
        }

        if (isset($data['country'])) {
            $data['country'] = strtoupper(substr($data['country'], 0, 10));
        }

        return $data;
    }

    protected function applyTransformations(array $data, ?array $rules = null)
    {
        if (empty($rules)) {
            return $data;
        }

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) || empty($rule['type'])) {
                continue;
            }

            switch ($rule['type']) {
                case 'uppercase':
                    $data[$field] = strtoupper($data[$field]);
                    break;
                case 'lowercase':
                    $data[$field] = strtolower($data[$field]);
                    break;
                case 'trim':
                    $data[$field] = trim($data[$field]);
                    break;
                case 'date_format':
                    try {
                        $data[$field] = Carbon::parse($data[$field])
                            ->format($rule['format'] ?? 'Y-m-d');
                    } catch (\Exception $e) {
                        // keep original value
                    }
                    break;
            }
        }

        return $data;
    }

    public function getUploadHistory(?int $departmentId = null)
    {
        $query = UploadHistory::with(['excelFormat', 'mappingConfiguration', 'uploader', 'department']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->orderBy('uploaded_at', 'desc')->get();
    }

    public function getUploadById(int $id, ?int $departmentId = null)
    {
        $query = UploadHistory::with(['excelFormat', 'mappingConfiguration', 'uploader', 'department']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->findOrFail($id);
    }

    /**
     * Insert rows dalam batch untuk menghindari limit 2100 parameter SQL Server.
     */
    public function insertChunked(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $first = reset($rows);
        $columnsCount = max(1, count(array_keys($first)));
        $maxParams = 2000;
        $maxRowsPerBatch = max(1, intdiv($maxParams, $columnsCount));
        $batches = array_chunk($rows, $maxRowsPerBatch);

        DB::beginTransaction();
        try {
            foreach ($batches as $batch) {
                DB::table($table)->insert($batch);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
