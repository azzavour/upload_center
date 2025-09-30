<?php

namespace App\Http\Controllers;

use App\Services\MappingService;
use App\Services\ExcelFormatService;
use App\Models\ExcelFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // ✅ TAMBAHKAN INI

class MappingController extends Controller
{
    protected $mappingService;
    protected $formatService;

    public function __construct(MappingService $mappingService, ExcelFormatService $formatService)
    {
        $this->middleware('auth');
        $this->mappingService = $mappingService;
        $this->formatService = $formatService;
    }

    public function index()
    {
        /** @var \App\Models\User $user */ // ✅ TAMBAHKAN INI
        $user = Auth::user(); // ✅ GANTI auth()->user() JADI Auth::user()
        
        if ($user->isAdmin()) {
            $mappings = \App\Models\MappingConfiguration::with('excelFormat', 'department')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $mappings = $this->mappingService->getAllMappingsByDepartment($user->department_id);
        }
        
        return view('mapping.index', compact('mappings'));
    }

    public function create(Request $request)
    {
        $formatId = $request->query('format_id');
        $format = $this->formatService->findFormatById($formatId);
        
        // Cek akses department
        /** @var \App\Models\User $user */ // ✅ TAMBAHKAN INI
        $user = Auth::user(); // ✅ GANTI auth()->user() JADI Auth::user()
        if (!$user->isAdmin() && $format->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this format.');
        }
        
        $excelColumns = session('excel_columns', []);

        return view('mapping.create', compact('format', 'excelColumns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'excel_format_id' => 'required|exists:excel_formats,id',
            'mapping_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transformation_rules' => 'nullable|array'
        ]);

        try {
            $columnMapping = [];
            
            if ($request->has('column_mapping') && is_string($request->column_mapping)) {
                $columnMapping = json_decode($request->column_mapping, true);
            }
            
            if (empty($columnMapping)) {
                return redirect()->back()
                    ->with('error', 'Column mapping tidak boleh kosong. Minimal isi satu mapping!')
                    ->withInput();
            }
            
            $transformationRules = [];
            if ($request->transformation_rules) {
                foreach ($request->transformation_rules as $field => $rule) {
                    if (!empty($rule['type'])) {
                        $transformationRules[$field] = $rule;
                    }
                }
            }

            /** @var \App\Models\User $user */ // ✅ TAMBAHKAN INI
            $user = Auth::user(); // ✅ GANTI auth()->user() JADI Auth::user()
            
            $mapping = $this->mappingService->createMapping(
                $request->excel_format_id,
                $columnMapping,
                $transformationRules ?: null,
                $user->department_id,
                $request->mapping_name,
                $request->description
            );

            return redirect()->route('mapping.index')
                ->with('success', 'Mapping "' . $mapping->mapping_name . '" berhasil dibuat! Index: ' . $mapping->mapping_index);
        } catch (\Exception $e) {
            Log::error('Mapping creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membuat mapping: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $mapping = \App\Models\MappingConfiguration::with('excelFormat', 'department')->findOrFail($id);
        
        // Cek akses
        /** @var \App\Models\User $user */ // ✅ TAMBAHKAN INI
        $user = Auth::user(); // ✅ GANTI auth()->user() JADI Auth::user()
        if (!$user->isAdmin() && $mapping->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this mapping.');
        }
        
        return view('mapping.show', compact('mapping'));
    }

    public function destroy($id)
    {
        try {
            $mapping = \App\Models\MappingConfiguration::findOrFail($id);
            
            // Cek akses
            /** @var \App\Models\User $user */ // ✅ TAMBAHKAN INI
            $user = Auth::user(); // ✅ GANTI auth()->user() JADI Auth::user()
            if (!$user->isAdmin() && $mapping->department_id !== $user->department_id) {
                abort(403, 'Unauthorized access.');
            }
            
            $mappingIndex = $mapping->mapping_index;
            
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