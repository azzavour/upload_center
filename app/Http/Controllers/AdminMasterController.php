<?php

namespace App\Http\Controllers;

use App\Services\MasterDataService;
use App\Services\DepartmentService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminMasterController extends Controller
{
    protected $masterDataService;
    protected $departmentService;
    protected $uploadService;

    public function __construct(
        MasterDataService $masterDataService,
        DepartmentService $departmentService,
        UploadService $uploadService
    ) {
        $this->middleware('admin');
        $this->masterDataService = $masterDataService;
        $this->departmentService = $departmentService;
        $this->uploadService = $uploadService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['department_id', 'source_table', 'start_date', 'end_date']);
        
        $masterData = $this->masterDataService->getAllMasterData($filters);
        $departments = $this->departmentService->getAllDepartments();
        
        return view('admin.master-data.index', compact('masterData', 'departments', 'filters'));
    }

    public function allUploads()
    {
        $histories = $this->uploadService->getUploadHistory(); // Tanpa filter department
        return view('admin.all-uploads', compact('histories'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['department_id', 'source_table', 'start_date', 'end_date']);
        
        $filename = $this->masterDataService->exportMasterDataToCsv($filters);
        
        if (!$filename) {
            return redirect()->back()->with('error', 'Tidak ada data untuk di-export.');
        }
        
        $path = storage_path('app/exports/' . $filename);
        
        return Response::download($path, $filename, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    public function detectDuplicates()
    {
        $duplicates = $this->masterDataService->detectDuplicateTables();
        return view('admin.duplicate-tables', compact('duplicates'));
    }
}