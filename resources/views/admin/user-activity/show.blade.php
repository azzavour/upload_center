@extends('layouts.app')

@section('title', 'User Activity Detail')

@section('content')
<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('admin.user-activity.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to User List
    </a>
    <a href="{{ route('admin.user-activity.export', $user->id) }}" class="btn btn-success">
        <i class="fas fa-file-export me-2"></i>Export to CSV
    </a>
</div>

<!-- User Info Card -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-2">
                    <i class="fas fa-user-circle text-primary me-2"></i>{{ $user->name }}
                </h3>
                <div class="mb-2">
                    <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                </div>
                <div class="mb-2">
                    <i class="fas fa-building me-2"></i>
                    @if($user->department)
                        <span class="badge badge-soft-info">{{ $user->department->name }} ({{ $user->department->code }})</span>
                    @else
                        <span class="badge badge-soft-neutral">No Department</span>
                    @endif
                </div>
                <div>
                    @if($user->role === 'admin')
                        <span class="badge badge-soft-danger"><i class="fas fa-crown me-1"></i>Admin</span>
                    @else
                        <span class="badge badge-soft-primary">User</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-muted small">Member since</div>
                <div class="h5">{{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card border-primary text-center">
            <div class="card-body">
                <h4 class="text-primary mb-0">{{ $stats['total_uploads'] }}</h4>
                <small class="text-muted">Total Uploads</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-info text-center">
            <div class="card-body">
                <h4 class="text-info mb-0">{{ number_format($stats['total_rows']) }}</h4>
                <small class="text-muted">Total Rows</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-success text-center">
            <div class="card-body">
                <h4 class="text-success mb-0">{{ number_format($stats['success_rows']) }}</h4>
                <small class="text-muted">Success</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-danger text-center">
            <div class="card-body">
                <h4 class="text-danger mb-0">{{ number_format($stats['failed_rows']) }}</h4>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-warning text-center">
            <div class="card-body">
                <h4 class="text-warning mb-0">{{ $stats['completed'] }}</h4>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-secondary text-center">
            <div class="card-body">
                <h4 class="text-secondary mb-0">{{ $stats['failed'] }}</h4>
                <small class="text-muted">Failed Uploads</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Errors -->
@if($recentErrors->isNotEmpty())
<div class="alert alert-warning" role="alert">
    <h6 class="alert-heading">
        <i class="fas fa-exclamation-triangle me-2"></i>Recent Errors (Last 5)
    </h6>
    <ul class="mb-0 small">
        @foreach($recentErrors as $error)
        <li>
            <strong>{{ $error->original_filename }}</strong> 
            ({{ $error->uploaded_at->format('d/m/Y H:i') }}) - 
            {{ $error->failed_rows }} failed rows
        </li>
        @endforeach
    </ul>
</div>
@endif

<!-- Upload History -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>Upload History
        </h5>
    </div>
    <div class="card-body">
        @if($uploads->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 mb-0">No uploads found</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Date & Time</th>
                        <th>Filename</th>
                        <th>Format</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Success</th>
                        <th>Failed</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploads as $upload)
                    <tr>
                        <td>{{ $loop->iteration + $uploads->firstItem() - 1 }}</td>
                        <td>
                            <div class="small">
                                {{ $upload->uploaded_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td>
                            <i class="fas fa-file-excel text-success me-1"></i>
                            {{ Str::limit($upload->original_filename, 25) }}
                        </td>
                        <td>
                            <span class="badge badge-soft-info small">{{ $upload->excelFormat->format_name }}</span>
                        </td>
                        <td>
                            @if($upload->upload_mode === 'replace')
                                <span class="badge badge-soft-warning small">Replace</span>
                            @else
                                <span class="badge badge-soft-success small">Append</span>
                            @endif
                        </td>
                        <td>
                            @if($upload->status === 'completed')
                                <span class="badge bg-success small">
                                    <i class="fas fa-check me-1"></i>Completed
                                </span>
                            @elseif($upload->status === 'failed')
                                <span class="badge bg-danger small">
                                    <i class="fas fa-times me-1"></i>Failed
                                </span>
                            @else
                                <span class="badge badge-soft-neutral small">{{ ucfirst($upload->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $upload->total_rows }}</td>
                        <td class="text-success">{{ $upload->success_rows }}</td>
                        <td class="text-danger">{{ $upload->failed_rows }}</td>
                        <td>
                            <a href="{{ route('history.show', $upload->id) }}" 
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $uploads->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Upload Pattern -->
@if($uploadsByDay->isNotEmpty())
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Upload Pattern by Day
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($uploadsByDay as $day)
            <div class="col-md-3 mb-2">
                <div class="p-2 border rounded text-center">
                    <strong>{{ $day->day_name }}</strong>
                    <div class="text-primary h4 mb-0">{{ $day->total }}</div>
                    <small class="text-muted">uploads</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection
