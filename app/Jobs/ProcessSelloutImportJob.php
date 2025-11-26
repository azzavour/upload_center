<?php

namespace App\Jobs;

use App\Models\ExcelFormat;
use App\Models\MappingConfiguration;
use App\Services\UploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSelloutImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $storedPath;
    protected string $originalFilename;
    protected int $formatId;
    protected ?int $mappingId;
    protected int $departmentId;
    protected ?int $userId;
    protected string $uploadMode;

    /**
     * @param string $storedPath path relatif di storage (mis. uploads/sellout/xxx.xlsx)
     */
    public function __construct(
        string $storedPath,
        string $originalFilename,
        int $formatId,
        ?int $mappingId,
        int $departmentId,
        ?int $userId,
        string $uploadMode = 'append'
    ) {
        $this->storedPath = $storedPath;
        $this->originalFilename = $originalFilename;
        $this->formatId = $formatId;
        $this->mappingId = $mappingId;
        $this->departmentId = $departmentId;
        $this->userId = $userId;
        $this->uploadMode = $uploadMode;
    }

    public function handle(UploadService $uploadService): void
    {
        Log::info('ProcessSelloutImportJob started', [
            'stored_path' => $this->storedPath,
            'format_id' => $this->formatId,
            'mapping_id' => $this->mappingId,
            'department_id' => $this->departmentId,
            'user_id' => $this->userId,
            'upload_mode' => $this->uploadMode,
        ]);

        $format = ExcelFormat::findOrFail($this->formatId);
        $mapping = $this->mappingId ? MappingConfiguration::find($this->mappingId) : null;

        $uploadService->processStoredFile(
            $this->storedPath,
            $this->originalFilename,
            $format,
            $mapping,
            $this->departmentId,
            $this->userId,
            $this->uploadMode
        );

        Log::info('ProcessSelloutImportJob completed', [
            'stored_path' => $this->storedPath,
            'history_for_format' => $this->formatId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessSelloutImportJob failed', [
            'stored_path' => $this->storedPath,
            'format_id' => $this->formatId,
            'error' => $exception->getMessage(),
        ]);
    }
}
