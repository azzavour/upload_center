<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UploadHistory;
use App\Models\Department;

class UserActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display all users with their activity summary
     */
    public function index(Request $request)
    {
        $query = User::with(['department'])
            ->withCount(['uploadHistories as total_uploads'])
            ->withSum(['uploadHistories as total_rows_uploaded'], 'success_rows');
        
        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'total_uploads');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortBy === 'total_uploads') {
            $query->orderBy('total_uploads', $sortDirection);
        } elseif ($sortBy === 'total_rows') {
            $query->orderBy('total_rows_uploaded', $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $users = $query->paginate(20);
        
        // Get departments for filter
        $departments = Department::active()->orderBy('name')->get();
        
        // Overall statistics
        $totalUsers = User::count();
        $activeUsers = User::whereHas('uploadHistories', function($q) {
            $q->where('uploaded_at', '>=', now()->subDays(30));
        })->count();
        
        $totalUploadsAllUsers = UploadHistory::count();
        $totalRowsAllUsers = UploadHistory::sum('success_rows');
        
        return view('admin.user-activity.index', compact(
            'users', 
            'departments',
            'totalUsers',
            'activeUsers',
            'totalUploadsAllUsers',
            'totalRowsAllUsers'
        ));
    }

    /**
     * Show detailed activity for specific user
     */
    public function show($userId)
    {
        $user = User::with('department')->findOrFail($userId);
        
        $uploads = UploadHistory::with(['excelFormat', 'department', 'mappingConfiguration'])
            ->where('uploaded_by', $userId)
            ->orderBy('uploaded_at', 'desc')
            ->paginate(20);
        
        // User statistics
        $stats = [
            'total_uploads' => UploadHistory::where('uploaded_by', $userId)->count(),
            'total_rows' => UploadHistory::where('uploaded_by', $userId)->sum('total_rows'),
            'success_rows' => UploadHistory::where('uploaded_by', $userId)->sum('success_rows'),
            'failed_rows' => UploadHistory::where('uploaded_by', $userId)->sum('failed_rows'),
            'completed' => UploadHistory::where('uploaded_by', $userId)->where('status', 'completed')->count(),
            'failed' => UploadHistory::where('uploaded_by', $userId)->where('status', 'failed')->count(),
        ];
        
        // Recent errors
        $recentErrors = UploadHistory::where('uploaded_by', $userId)
            ->where(function($q) {
                $q->where('status', 'failed')
                  ->orWhere('failed_rows', '>', 0);
            })
            ->orderBy('uploaded_at', 'desc')
            ->limit(5)
            ->get();
        
        // Upload frequency by day of week
        $uploadsByDay = UploadHistory::where('uploaded_by', $userId)
            ->select(
                DB::raw('DAYNAME(uploaded_at) as day_name'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('day_name')
            ->get();
        
        return view('admin.user-activity.show', compact(
            'user', 
            'uploads', 
            'stats', 
            'recentErrors',
            'uploadsByDay'
        ));
    }

    /**
     * Export user activity to CSV
     */
    public function export($userId)
    {
        $user = User::findOrFail($userId);
        
        $uploads = UploadHistory::with(['excelFormat', 'department'])
            ->where('uploaded_by', $userId)
            ->orderBy('uploaded_at', 'desc')
            ->get();
        
        $filename = 'user_activity_' . str_replace(' ', '_', $user->name) . '_' . date('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);
        
        // Ensure exports directory exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }
        
        $file = fopen($path, 'w');
        
        // Header
        fputcsv($file, [
            'Upload Date',
            'Filename',
            'Format',
            'Department',
            'Status',
            'Upload Mode',
            'Total Rows',
            'Success Rows',
            'Failed Rows',
            'Success Rate (%)',
            'Mapping Used',
        ]);
        
        // Rows
        foreach ($uploads as $upload) {
            $successRate = $upload->total_rows > 0 
                ? round(($upload->success_rows / $upload->total_rows) * 100, 2) 
                : 0;
            
            fputcsv($file, [
                $upload->uploaded_at->format('Y-m-d H:i:s'),
                $upload->original_filename,
                $upload->excelFormat->format_name,
                $upload->department->name ?? 'N/A',
                $upload->status,
                $upload->upload_mode,
                $upload->total_rows,
                $upload->success_rows,
                $upload->failed_rows,
                $successRate,
                $upload->mappingConfiguration->mapping_index ?? 'Standard',
            ]);
        }
        
        fclose($file);
        
        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
}