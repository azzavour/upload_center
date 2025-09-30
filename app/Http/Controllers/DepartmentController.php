<?php

namespace App\Http\Controllers;

use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->middleware('admin');
        $this->departmentService = $departmentService;
    }

    public function index()
    {
        $departments = $this->departmentService->getAllDepartments();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string'
        ]);

        $department = $this->departmentService->createDepartment($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department berhasil dibuat!');
    }

    public function edit($id)
    {
        $department = $this->departmentService->findDepartmentById($id);
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $department = $this->departmentService->updateDepartment($id, $validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department berhasil diupdate!');
    }
}