<?php

namespace App\Http\Controllers;

use App\Services\ExcelFormatService;
use Illuminate\Http\Request;

class ExcelFormatController extends Controller
{
    protected $formatService;

    public function __construct(ExcelFormatService $formatService)
    {
        $this->formatService = $formatService;
    }

    public function index()
    {
        $formats = $this->formatService->getAllFormats();
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

        $format = $this->formatService->createFormat($validated);

        return redirect()->route('formats.index')
            ->with('success', 'Format berhasil didaftarkan!');
    }
}