<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadHistory;
use App\Models\User;
use App\Models\ExcelFormat;

class DepartmentUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display department's upload history (all users in same department)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validasi user punya department
        if (!$user->hasDepartment() && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di department manapun.');
        }
        
        $departmentId = $user->department_id;
        
        // Query uploads dari department yang sama
        $query = UploadHistory::with(['excelFormat', 'department', 'mappingConfiguration', 'uploader'])
            ->where('department_id', $departmentId)
            ->orderBy('uploaded_at', 'desc');
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('uploaded_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('uploaded_at', '<=', $request->end_date);
        }
        
        // Filter by upload mode
        if ($request->has('upload_mode') && $request->upload_mode != '') {
            $query->where('upload_mode', $request->upload_mode);
        }
        
        // Filter by format
        if ($request->has('format_id') && $request->format_id != '') {
            $query->where('excel_format_id', $request->format_id);
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('uploaded_by', $request->user_id);
        }
        
        $uploads = $query->paginate(20);
        
        // Get statistics for department
        $stats = $this->getDepartmentStats($departmentId);
        
        // Get users in department untuk filter dropdown
        $users = User::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
        
        // Get formats in department untuk filter dropdown
        $formats = ExcelFormat::where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('format_name')
            ->get();
        
        return view('department-uploads.index', compact('uploads', 'stats', 'user', 'users', 'formats'));
    }

    /**
     * Show department statistics
     */
    public function stats()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->hasDepartment() && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di department manapun.');
        }
        
        $departmentId = $user->department_id;
        
        $stats = $this->getDepartmentStats($departmentId);
        
        // Monthly upload trend (last 6 months)
        $monthlyTrend = UploadHistory::where('department_id', $departmentId)
            ->select(
                DB::raw('DATE_FORMAT(uploaded_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_uploads'),
                DB::raw('SUM(success_rows) as total_success'),
                DB::raw('SUM(failed_rows) as total_failed')
            )
            ->where('uploaded_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
        
        // Upload by format
        $uploadsByFormat = UploadHistory::where('department_id', $departmentId)
            ->join('excel_formats', 'upload_histories.excel_format_id', '=', 'excel_formats.id')
            ->select(
                'excel_formats.format_name',
                DB::raw('COUNT(*) as total_uploads'),
                DB::raw('SUM(upload_histories.success_rows) as total_rows')
            )
            ->groupBy('excel_formats.id', 'excel_formats.format_name')
            ->orderBy('total_uploads', 'desc')
            ->get();
        
        // Upload by user (top uploaders in department)
        $uploadsByUser = UploadHistory::where('department_id', $departmentId)
            ->join('users', 'upload_histories.uploaded_by', '=', 'users.id')
            ->select(
                'users.name',
                'users.email',
                DB::raw('COUNT(*) as total_uploads'),
                DB::raw('SUM(upload_histories.success_rows) as total_rows')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_uploads', 'desc')
            ->get();
        
        return view('department-uploads.stats', compact(
            'stats', 
            'monthlyTrend', 
            'uploadsByFormat', 
            'uploadsByUser',
            'user'
        ));
    }

    /**
     * Download uploaded file
     */
    public function download($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $upload = UploadHistory::findOrFail($id);
        
        // Security check: user hanya bisa download dari department sendiri
        if (!$user->isAdmin() && $upload->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $filePath = 'uploads/' . $upload->stored_filename;
        
        if (!Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di storage.');
        }
        
        return Storage::download($filePath, $upload->original_filename);
    }

    /**
     * Calculate department statistics
     */
    private function getDepartmentStats($departmentId)
    {
        $totalUploads = UploadHistory::where('department_id', $departmentId)->count();
        
        $totalRowsProcessed = UploadHistory::where('department_id', $departmentId)
            ->sum('total_rows');
        
        $totalSuccessRows = UploadHistory::where('department_id', $departmentId)
            ->sum('success_rows');
        
        $totalFailedRows = UploadHistory::where('department_id', $departmentId)
            ->sum('failed_rows');
        
        $completedUploads = UploadHistory::where('department_id', $departmentId)
            ->where('status', 'completed')
            ->count();
        
        $failedUploads = UploadHistory::where('department_id', $departmentId)
            ->where('status', 'failed')
            ->count();
        
        $lastUpload = UploadHistory::where('department_id', $departmentId)
            ->orderBy('uploaded_at', 'desc')
            ->first();
        
        $successRate = $totalUploads > 0 ? ($completedUploads / $totalUploads) * 100 : 0;
        $dataAccuracy = $totalRowsProcessed > 0 ? ($totalSuccessRows / $totalRowsProcessed) * 100 : 0;
        
        // Total active users in department
        $activeUsers = UploadHistory::where('department_id', $departmentId)
            ->distinct('uploaded_by')
            ->count('uploaded_by');
        
        return [
            'total_uploads' => $totalUploads,
            'total_rows_processed' => $totalRowsProcessed,
            'total_success_rows' => $totalSuccessRows,
            'total_failed_rows' => $totalFailedRows,
            'completed_uploads' => $completedUploads,
            'failed_uploads' => $failedUploads,
            'success_rate' => round($successRate, 2),
            'data_accuracy' => round($dataAccuracy, 2),
            'last_upload' => $lastUpload,
            'active_users' => $activeUsers,
        ];
    }
}