<?php

namespace App\Http\Controllers;

use App\Models\UploadHistory;
use App\Models\User;
use App\Models\ExcelFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DepartmentUploadsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all uploads from user's department
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasDepartment()) {
            return redirect()->route('upload.index')
                ->with('error', 'Anda tidak terdaftar di department manapun.');
        }

        $departmentId = $user->department_id;

        // Query uploads from same department
        $query = UploadHistory::with(['excelFormat', 'uploader', 'department'])
            ->where('department_id', $departmentId);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('uploaded_by', $request->user_id);
        }

        if ($request->filled('format_id')) {
            $query->where('excel_format_id', $request->format_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('uploaded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('uploaded_at', '<=', $request->end_date);
        }

        $uploads = $query->orderBy('uploaded_at', 'desc')->paginate(20);

        // Get users from same department for filter
        $users = User::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        // Get formats from same department for filter
        $formats = ExcelFormat::where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('format_name')
            ->get();

        // Calculate statistics
        $stats = [
            'total_uploads' => UploadHistory::where('department_id', $departmentId)->count(),
            'total_success_rows' => UploadHistory::where('department_id', $departmentId)->sum('success_rows'),
            'active_users' => User::where('department_id', $departmentId)
                ->whereHas('uploadHistories', function($q) {
                    $q->where('uploaded_at', '>=', now()->subDays(30));
                })
                ->count(),
            'success_rate' => $this->calculateSuccessRate($departmentId),
            'last_upload' => UploadHistory::with('uploader')
                ->where('department_id', $departmentId)
                ->latest('uploaded_at')
                ->first()
        ];

        return view('department-uploads.index', compact('uploads', 'users', 'formats', 'stats', 'user'));
    }

    /**
     * Show statistics page
     */
    public function stats()
    {
        $user = Auth::user();
        
        if (!$user->hasDepartment()) {
            return redirect()->route('upload.index')
                ->with('error', 'Anda tidak terdaftar di department manapun.');
        }

        $departmentId = $user->department_id;

        // Overall statistics
        $stats = [
            'total_uploads' => UploadHistory::where('department_id', $departmentId)->count(),
            'total_success_rows' => UploadHistory::where('department_id', $departmentId)->sum('success_rows'),
            'total_failed_rows' => UploadHistory::where('department_id', $departmentId)->sum('failed_rows'),
            'active_users' => User::where('department_id', $departmentId)
                ->whereHas('uploadHistories')
                ->count(),
            'data_accuracy' => $this->calculateSuccessRate($departmentId)
        ];

        // ✅ FIX: Monthly trend - PostgreSQL compatible
        $monthlyTrend = UploadHistory::where('department_id', $departmentId)
            ->where('uploaded_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("TO_CHAR(uploaded_at, 'YYYY-MM') as month"),
                DB::raw("COUNT(*) as total_uploads"),
                DB::raw("SUM(success_rows) as total_success"),
                DB::raw("SUM(failed_rows) as total_failed")
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Upload by format
        $uploadsByFormat = UploadHistory::where('upload_histories.department_id', $departmentId)
            ->join('excel_formats', 'upload_histories.excel_format_id', '=', 'excel_formats.id')
            ->select(
                'excel_formats.format_name',
                DB::raw('COUNT(*) as total_uploads'),
                DB::raw('SUM(upload_histories.success_rows) as total_rows')
            )
            ->groupBy('excel_formats.format_name')
            ->orderBy('total_uploads', 'desc')
            ->get();

        // ✅ NEW: Upload by user in department
        $uploadsByUser = User::where('users.department_id', $departmentId)
            ->join('upload_histories', 'users.id', '=', 'upload_histories.uploaded_by')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(upload_histories.id) as total_uploads'),
                DB::raw('SUM(upload_histories.success_rows) as total_rows')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_uploads', 'desc')
            ->get();

        return view('department-uploads.stats', compact(
            'user',
            'stats',
            'monthlyTrend',
            'uploadsByFormat',
            'uploadsByUser'
        ));
    }

    /**
     * Download original file
     */
    public function download($id)
    {
        $user = Auth::user();
        $upload = UploadHistory::findOrFail($id);

        // Check department access
        if (!$user->isAdmin() && $upload->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this file.');
        }

        $filePath = 'uploads/' . $upload->stored_filename;

        if (!Storage::exists($filePath)) {
            return redirect()->back()
                ->with('error', 'File tidak ditemukan di server.');
        }

        return Storage::download($filePath, $upload->original_filename);
    }

    /**
     * Calculate success rate for department
     */
    private function calculateSuccessRate($departmentId)
    {
        $totals = UploadHistory::where('department_id', $departmentId)
            ->selectRaw('SUM(success_rows) as success, SUM(total_rows) as total')
            ->first();

        if (!$totals || $totals->total == 0) {
            return 0;
        }

        return round(($totals->success / $totals->total) * 100, 1);
    }
}