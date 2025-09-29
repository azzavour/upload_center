<?php

namespace App\Http\Controllers;

use App\Services\MappingService;
use App\Services\ExcelFormatService;
use App\Models\ExcelFormat;
use Illuminate\Http\Request;

class MappingController extends Controller
{
    protected $mappingService;
    protected $formatService;

    public function __construct(MappingService $mappingService, ExcelFormatService $formatService)
    {
        $this->mappingService = $mappingService;
        $this->formatService = $formatService;
    }

    public function index()
    {
        $mappings = \App\Models\MappingConfiguration::with('excelFormat')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('mapping.index', compact('mappings'));
    }

    public function create(Request $request)
    {
        $formatId = $request->query('format_id');
        $format = $this->formatService->findFormatById($formatId);
        
        // Ambil excel columns dari session jika ada
        $excelColumns = session('excel_columns', []);

        return view('mapping.create', compact('format', 'excelColumns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'excel_format_id' => 'required|exists:excel_formats,id',
            'column_mapping' => 'required|json',
            'transformation_rules' => 'nullable|array'
        ]);

        try {
            $columnMapping = json_decode($request->column_mapping, true);
            
            // Filter transformation rules yang kosong
            $transformationRules = [];
            if ($request->transformation_rules) {
                foreach ($request->transformation_rules as $field => $rule) {
                    if (!empty($rule['type'])) {
                        $transformationRules[$field] = $rule;
                    }
                }
            }

            $mapping = $this->mappingService->createMapping(
                $request->excel_format_id,
                $columnMapping,
                $transformationRules ?: null
            );

            return redirect()->route('mapping.index')
                ->with('success', 'Mapping berhasil dibuat! Mapping Index: ' . $mapping->mapping_index);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat mapping: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $mapping = \App\Models\MappingConfiguration::with('excelFormat')->findOrFail($id);
        return view('mapping.show', compact('mapping'));
    }

    public function destroy($id)
    {
        try {
            $mapping = \App\Models\MappingConfiguration::findOrFail($id);
            $mappingIndex = $mapping->mapping_index;
            
            // Cek apakah mapping sedang digunakan di upload history
            $usageCount = \App\Models\UploadHistory::where('mapping_configuration_id', $id)->count();
            
            if ($usageCount > 0) {
                return redirect()->route('mapping.index')
                    ->with('error', 'Mapping ' . $mappingIndex . ' tidak dapat dihapus karena masih digunakan di ' . $usageCount . ' upload history.');
            }
            
            $mapping->delete();
            
            return redirect()->route('mapping.index')
                ->with('success', 'Mapping ' . $mappingIndex . ' berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('mapping.index')
                ->with('error', 'Gagal menghapus mapping: ' . $e->getMessage());
        }
    }
}