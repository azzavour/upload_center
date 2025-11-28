@extends('layouts.app')

@section('title', 'Manage Departments')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-building text-primary me-2"></i>Manage Departments
            </h2>
            <p class="text-muted mb-0 mt-2">Administer company departments and divisions.</p>
        </div>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Department
        </a>
    </div>

    <div class="card-body">
        @if($departments->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-building text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No departments have been registered yet.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $dept->code }}</code></td>
                        <td><strong>{{ $dept->name }}</strong></td>
                        <td>{{ Str::limit($dept->description, 50) }}</td>
                        <td>
                            <span class="badge badge-soft-info">
                                {{ $dept->users->count() }} users
                            </span>
                        </td>
                        <td>
                            @if($dept->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge badge-soft-neutral">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.departments.edit', $dept->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
