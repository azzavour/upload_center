<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use App\Services\TableManagerService;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class HistoryController extends Controller
{
    protected $uploadService;
    protected $tableManager;

    public function __construct(UploadService $uploadService, TableManagerService $tableManager)
    {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
        $this->tableManager = $tableManager;
    }

    public function index()
    {
        $user = Auth::user();

        // Admin bisa lihat semua, user hanya lihat department sendiri
        $departmentId = $user->isAdmin() ? null : $user->department_id;

        $histories = $this->uploadService->getUploadHistory($departmentId);
        return view('history.index', compact('histories'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;

        $history = $this->uploadService->getUploadById($id, $departmentId);

        // Siapkan paginator kosong agar view aman memanggil total()/firstItem()/lastItem()
        $importedData = new LengthAwarePaginator([], 0, 20, request('page', 1), [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
        $tableColumns = [];
        $targetTable = null;

        if (
            $history->status === 'completed' &&
            $history->success_rows > 0 &&
            $history->excelFormat &&
            $history->department
        ) {
            // 1) Coba ambil nama tabel langsung dari FileUpload (paling akurat)
            $fileUpload = FileUpload::where('upload_history_id', $history->id)->first();
            if ($fileUpload && $fileUpload->target_table) {
                $targetTable = $fileUpload->target_table;
            } else {
                // 2) Fallback: gunakan TableManagerService (nama ter-normalisasi)
                $targetTable = $this->tableManager->getActualTableName(
                    $history->excelFormat->target_table,
                    $history->department_id
                );
            }

            if ($targetTable && Schema::hasTable($targetTable)) {
                $tableColumns = Schema::getColumnListing($targetTable);

                // Query utama: filter dengan upload_history_id
                $query = DB::table($targetTable)
                    ->where('upload_history_id', $history->id)
                    ->orderBy('created_at', 'desc');

                $countByHistory = (clone $query)->count();

                if ($countByHistory === 0) {
                    // Fallback heuristik: gunakan window waktu sekitar uploaded_at + filter department
                    $windowStart = Carbon::parse($history->uploaded_at)->subMinutes(30);
                    $windowEnd   = Carbon::parse($history->uploaded_at)->addMinutes(30);

                    $query = DB::table($targetTable)
                        ->where('department_id', $history->department_id)
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->orderBy('created_at', 'desc');
                }

                $importedData = $query->paginate(20)->withQueryString();
            }
        }

        return view('history.show', compact('history', 'importedData', 'tableColumns', 'targetTable'));
    }

    /**
     * Batalkan proses upload (menandai failed dan mencegah inser lebih lanjut).
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        $history = $this->uploadService->getUploadById($id, $departmentId);

        if (in_array($history->status, ['completed', 'failed'])) {
            return redirect()->back()->with('error', 'Upload ini sudah selesai atau gagal.');
        }

        $details = $history->error_details ?? [];
        if (!is_array($details)) {
            $details = [];
        }
        $details[] = [
            'message' => 'Dibatalkan oleh user',
            'time' => now()->toDateTimeString()
        ];

        $history->update([
            'status' => 'failed',
            'error_details' => $details
        ]);

        // Opsional: hapus job pending yang memuat stored_filename di payload
        if ($history->stored_filename) {
            DB::table('jobs')
                ->where('payload', 'like', '%' . $history->stored_filename . '%')
                ->delete();
        }

        return redirect()->route('history.index')->with('success', 'Proses upload dibatalkan.');
    }
}
