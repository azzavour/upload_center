<?php

namespace App\Http\Controllers;

use App\Services\ExcelFormatService;
use Illuminate\Http\Request;

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
        $user = auth()->user();
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
        $validated = $request->validate([
            'format_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expected_columns' => 'required|array',
            'expected_columns.*' => 'required|string',
            'target_table' => 'required|string'
        ]);

        $user = auth()->user();
        
        if (!$user->hasDepartment() && !$user->isAdmin()) {
            return redirect()->back()
                ->with('error', 'Anda belum terdaftar di department manapun.');
        }

        $format = $this->formatService->createFormat(
            $validated, 
            $user->department_id
        );

        return redirect()->route('formats.index')
            ->with('success', 'Format berhasil didaftarkan! Tabel "' . $format->target_table . '" telah dibuat.');
    }
}