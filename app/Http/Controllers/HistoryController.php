<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
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
        
        // ✅ TAMBAHAN: Ambil data yang di-import ke database
        $importedData = null;
        $tableColumns = [];
        $targetTable = null;
        
        if ($history->status === 'completed' && $history->success_rows > 0) {
            // Get actual table name dengan department prefix
            $format = $history->excelFormat;
            $department = $history->department;
            
            if ($department && $format) {
                $deptCode = strtolower($department->code);
                $targetTable = "dept_{$deptCode}_{$format->target_table}";
                
                // Cek apakah tabel ada
                if (\Schema::hasTable($targetTable)) {
                    // Get columns
                    $tableColumns = \Schema::getColumnListing($targetTable);
                    
                    // Get data (paginate 20 rows)
                    $importedData = \DB::table($targetTable)
                        ->where('upload_history_id', $history->id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
                }
            }
        }
        
        return view('history.show', compact('history', 'importedData', 'tableColumns', 'targetTable'));
    }
}
