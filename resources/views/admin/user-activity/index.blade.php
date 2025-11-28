@extends('layouts.app')

@section('title', 'User Activity Monitoring')

@section('content')
<!-- Overall Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $totalUsers }}</h3>
                <small class="text-muted">Total Users</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $activeUsers }}</h3>
                <small class="text-muted">Active (30 days)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-upload text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ number_format($totalUploadsAllUsers) }}</h3>
                <small class="text-muted">Total Uploads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-database text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ number_format($totalRowsAllUsers) }}</h3>
                <small class="text-muted">Total Rows</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-chart-line text-primary me-2"></i>User Activity Monitoring
        </h2>
        <p class="text-muted mb-0 mt-2">Monitor every user's upload activity.</p>
    </div>

    <div class="card-body">
        <!-- Filters & Sort -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Department</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="total_uploads" {{ request('sort_by') == 'total_uploads' ? 'selected' : '' }}>Uploads</option>
                        <option value="total_rows" {{ request('sort_by') == 'total_rows' ? 'selected' : '' }}>Rows</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Direction</label>
                    <select name="sort_direction" class="form-select form-select-sm">
                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Apply Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Users Table -->
        @if($users->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No users found</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th class="text-center">Total Uploads</th>
                        <th class="text-center">Total Rows</th>
                        <th class="text-center">Last Activity</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                        <td>
                            <div>
                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                <strong>{{ $user->name }}</strong>
                            </div>
                            <small class="text-muted">{{ $user->email }}</small>
                        </td>
                        <td>
                            @if($user->department)
                                <span class="badge badge-soft-info">{{ $user->department->code }}</span>
                            @else
                                <span class="badge badge-soft-neutral">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge badge-soft-danger">
                                    <i class="fas fa-crown me-1"></i>Admin
                                </span>
                            @else
                                <span class="badge badge-soft-primary">User</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-soft-info fs-6">
                                {{ $user->total_uploads ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-soft-info fs-6">
                                {{ number_format($user->total_rows_uploaded ?? 0) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $lastUpload = $user->uploadHistories()->latest('uploaded_at')->first();
                            @endphp
                            @if($lastUpload)
                                <small class="text-muted">
                                    {{ $lastUpload->uploaded_at->diffForHumans() }}
                                </small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.user-activity.show', $user->id) }}" 
                                    class="btn btn-outline-primary"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.user-activity.export', $user->id) }}" 
                                    class="btn btn-outline-success"
                                    title="Export CSV">
                                    <i class="fas fa-file-export"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
