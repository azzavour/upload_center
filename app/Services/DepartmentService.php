<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Str;

class DepartmentService
{
    public function getAllDepartments()
    {
        return Department::active()->orderBy('name')->get();
    }

    public function createDepartment(array $data)
    {
        $data['code'] = $data['code'] ?? Str::slug($data['name']);
        return Department::create($data);
    }

    public function findDepartmentById(int $id)
    {
        return Department::findOrFail($id);
    }

    public function updateDepartment(int $id, array $data)
    {
        $department = $this->findDepartmentById($id);
        $department->update($data);
        return $department;
    }

    public function getUserDepartment($user)
    {
        return $user->department;
    }
}