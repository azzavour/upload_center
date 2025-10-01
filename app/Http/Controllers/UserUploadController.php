<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UploadHistory;

class UserUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's upload history with detailed tracking
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = UploadHistory::with(['excelFormat', 'department', 'mappingConfiguration'])
            ->where('uploaded_by', $user->id)
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
        
        $uploads = $query->paginate(20);
        
        // Get statistics
        $stats = $this->getUserStats($user->id);
        
        return view('my-uploads.index', compact('uploads', 'stats', 'user'));
    }

    /**
     * Get user upload statistics
     */
    public function stats()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $stats = $this->getUserStats($user->id);
        
        // Monthly upload trend (last 6 months)
        $monthlyTrend = UploadHistory::where('uploaded_by', $user->id)
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
        $uploadsByFormat = UploadHistory::where('uploaded_by', $user->id)
            ->join('excel_formats', 'upload_histories.excel_format_id', '=', 'excel_formats.id')
            ->select(
                'excel_formats.format_name',
                DB::raw('COUNT(*) as total_uploads'),
                DB::raw('SUM(upload_histories.success_rows) as total_rows')
            )
            ->groupBy('excel_formats.id', 'excel_formats.format_name')
            ->orderBy('total_uploads', 'desc')
            ->get();
        
        return view('my-uploads.stats', compact('stats', 'monthlyTrend', 'uploadsByFormat', 'user'));
    }

    /**
     * Calculate user statistics
     */
    private function getUserStats($userId)
    {
        $totalUploads = UploadHistory::where('uploaded_by', $userId)->count();
        
        $totalRowsProcessed = UploadHistory::where('uploaded_by', $userId)
            ->sum('total_rows');
        
        $totalSuccessRows = UploadHistory::where('uploaded_by', $userId)
            ->sum('success_rows');
        
        $totalFailedRows = UploadHistory::where('uploaded_by', $userId)
            ->sum('failed_rows');
        
        $completedUploads = UploadHistory::where('uploaded_by', $userId)
            ->where('status', 'completed')
            ->count();
        
        $failedUploads = UploadHistory::where('uploaded_by', $userId)
            ->where('status', 'failed')
            ->count();
        
        $lastUpload = UploadHistory::where('uploaded_by', $userId)
            ->orderBy('uploaded_at', 'desc')
            ->first();
        
        $successRate = $totalUploads > 0 ? ($completedUploads / $totalUploads) * 100 : 0;
        $dataAccuracy = $totalRowsProcessed > 0 ? ($totalSuccessRows / $totalRowsProcessed) * 100 : 0;
        
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
        ];
    }
}