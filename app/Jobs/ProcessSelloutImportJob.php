<?php

namespace App\Jobs;

use App\Models\UploadHistory;
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

    public ?int $historyId;
    public ?string $filePath;

    public function __construct(int $historyId, string $filePath)
    {
        $this->historyId = $historyId;
        $this->filePath = $filePath;
    }

    public function handle(UploadService $uploadService): void
    {
        if (!$this->historyId || !$this->filePath) {
            Log::warning('Upload job missing payload data', [
                'history_id' => $this->historyId,
                'file_path' => $this->filePath,
            ]);
            return;
        }

        $history = UploadHistory::with(['excelFormat', 'mappingConfiguration'])->find($this->historyId);
        if (! $history || ! $history->excelFormat) {
            return;
        }

        try {
            $history->status = 'processing';
            $history->total_rows = $history->total_rows ?? 0;
            $history->success_rows = $history->success_rows ?? 0;
            $history->failed_rows = $history->failed_rows ?? 0;
            $history->save();

            $processedRows = $uploadService->processStoredFile(
                $history,
                $this->filePath,
                $history->excelFormat,
                $history->mappingConfiguration,
                $history->department_id,
                $history->uploaded_by,
                $history->upload_mode ?? 'append'
            );

            $history->refresh();

            if (is_int($processedRows)) {
                $history->success_rows = $processedRows;
                $history->total_rows = max($history->total_rows, $processedRows + $history->failed_rows);
            }

            $history->status = 'completed';
            $history->save();

            Log::info('Upload job finished', [
                'history_id' => $history->id,
                'status' => $history->status,
                'rows' => $history->success_rows,
            ]);
        } catch (\Throwable $e) {
            $history->status = 'failed';
            $history->error_details = ['message' => $e->getMessage()];
            $history->save();

            Log::error('Upload job failed', [
                'history_id' => $history->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
