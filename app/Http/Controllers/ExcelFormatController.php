<?php

namespace App\Http\Controllers;

use App\Services\ExcelFormatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExcelFormatController extends Controller
{
    protected $formatService;

    public function __construct(ExcelFormatService $formatService)
    {
        $this->middleware('auth');
        $this->formatService = $formatService;
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $formats = $this->formatService->getAllFormats($departmentId);
        return view('formats.index', compact('formats'));
    }

    public function create()
    {
        return view('formats.create');
    }

    public function store(Request $request)
    {
        Log::info('Format store request received', [
            'data' => $request->all()
        ]);

        $validated = $request->validate([
            'format_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expected_columns' => 'required|array',
            'expected_columns.*' => 'required|string',
            'target_table' => 'required|string|regex:/^[a-z0-9_]+$/'
        ], [
            'target_table.regex' => 'Nama tabel hanya boleh mengandung huruf kecil, angka, dan underscore (_)'
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        Log::info('User info', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'department_id' => $user->department_id,
            'has_department' => $user->hasDepartment()
        ]);
        
        if (!$user->hasDepartment() && !$user->isAdmin()) {
            Log::warning('User has no department');
            return redirect()->back()
                ->with('error', 'Anda belum terdaftar di department manapun.');
        }

        try {
            Log::info('Creating format', [
                'department_id' => $user->department_id
            ]);

            $format = $this->formatService->createFormat(
                $validated, 
                $user->department_id
            );

            Log::info('Format created successfully', [
                'format_id' => $format->id,
                'format_name' => $format->format_name
            ]);

            // Get actual table name for display
            $actualTableName = $this->formatService->getActualTableName($format);

            Log::info('Redirecting to index', [
                'table_name' => $actualTableName
            ]);

            return redirect()->route('formats.index')
                ->with('success', 'Format berhasil didaftarkan! Tabel "' . $actualTableName . '" telah dibuat di database.');
        } catch (\Exception $e) {
            Log::error('Format creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat format: ' . $e->getMessage());
        }
    }
}
