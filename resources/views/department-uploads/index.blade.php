@extends('layouts.app')

@section('title', 'Department Uploads')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-upload text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['total_uploads'] }}</h3>
                <small class="text-muted">Total Uploads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ number_format($stats['total_success_rows']) }}</h3>
                <small class="text-muted">Rows Processed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-users text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['active_users'] }}</h3>
                <small class="text-muted">Active Users</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-percentage text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['success_rate'] }}%</h3>
                <small class="text-muted">Success Rate</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-building text-primary me-2"></i>Department Uploads
            </h2>
            <p class="text-muted mb-0 mt-2">
                <i class="fas fa-info-circle me-1"></i>
                Showing every upload submitted by the <strong>{{ $user->department->name }}</strong> department.
            </p>
        </div>
        <div>
            <a href="{{ route('department-uploads.stats') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-2"></i>View Statistics
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <!-- Filter by User -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="fas fa-user me-1"></i>User
                    </label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filter by Format -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="fas fa-file-excel me-1"></i>Format
                    </label>
                    <select name="format_id" class="form-select form-select-sm">
                        <option value="">All Formats</option>
                        @foreach($formats as $format)
                            <option value="{{ $format->id }}" {{ request('format_id') == $format->id ? 'selected' : '' }}>
                                {{ $format->format_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filter by Status -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold">
                        <i class="fas fa-check-circle me-1"></i>Status
                    </label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    </select>
                </div>
                
                <!-- Filter by Date Range -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold">
                        <i class="fas fa-calendar me-1"></i>Start Date
                    </label>
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                        value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-bold">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" 
                        value="{{ request('end_date') }}">
                </div>
            </div>
            
            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('department-uploads.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Last Upload Info -->
        @if($stats['last_upload'])
        <div class="alert alert-info" role="alert">
            <i class="fas fa-clock me-2"></i>
            <strong>Last Upload:</strong> 
            {{ $stats['last_upload']->original_filename }} 
            by <strong>{{ $stats['last_upload']->uploader->name }}</strong>
            ({{ $stats['last_upload']->uploaded_at->diffForHumans() }})
        </div>
        @endif

        <!-- Upload Table -->
        @if($uploads->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No uploads have been submitted for this department.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Date & Time</th>
                        <th>Uploaded By</th>
                        <th>Filename</th>
                        <th>Format</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Rows</th>
                        <th>Accuracy</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploads as $upload)
                    <tr>
                        <td>{{ $loop->iteration + $uploads->firstItem() - 1 }}</td>
                        <td>
                            <div class="small">
                                <div>{{ $upload->uploaded_at->format('d/m/Y') }}</div>
                                <div class="text-muted">{{ $upload->uploaded_at->format('H:i:s') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle text-primary me-2"></i>
                                <div>
                                    <div class="fw-bold small">{{ $upload->uploader->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $upload->uploader->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <i class="fas fa-file-excel text-success me-1"></i>
                            <span class="small">{{ Str::limit($upload->original_filename, 25) }}</span>
                        </td>
                        <td>
                            <span class="badge badge-soft-info small">
                                {{ $upload->excelFormat->format_name }}
                            </span>
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
                                    <i class="fas fa-check-circle me-1"></i>OK
                                </span>
                            @elseif($upload->status === 'failed')
                                <span class="badge bg-danger small">
                                    <i class="fas fa-times-circle me-1"></i>Failed
                                </span>
                            @else
                                <span class="badge badge-soft-neutral small">{{ ucfirst($upload->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">
                                    <i class="fas fa-check me-1"></i>{{ $upload->success_rows }}
                                </div>
                                @if($upload->failed_rows > 0)
                                <div class="text-danger">
                                    <i class="fas fa-times me-1"></i>{{ $upload->failed_rows }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $accuracy = $upload->total_rows > 0 
                                    ? round(($upload->success_rows / $upload->total_rows) * 100, 1) 
                                    : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $accuracy >= 90 ? 'bg-success' : ($accuracy >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                    style="width: {{ $accuracy }}%">
                                    {{ $accuracy }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('history.show', $upload->id) }}" 
                                    class="btn btn-outline-primary"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('department-uploads.download', $upload->id) }}" 
                                    class="btn btn-outline-success"
                                    title="Download File">
                                    <i class="fas fa-download"></i>
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
            {{ $uploads->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Info Box -->
<div class="alert alert-light border mt-4" role="alert">
    <div class="d-flex align-items-start">
        <i class="fas fa-info-circle text-primary me-3 mt-1" style="font-size: 1.5rem;"></i>
        <div>
            <h6 class="alert-heading mb-2">
                <strong>About Department Uploads</strong>
            </h6>
            <ul class="mb-0 small">
                <li>Review <strong>every upload submitted by users in your department.</strong></li>
                <li>Use the filters to find uploads by user, format, or date range.</li>
                <li>Select <i class="fas fa-eye"></i> to view the detailed upload report.</li>
                <li>Select <i class="fas fa-download"></i> to download the original file.</li>
                <li><strong>Read-only:</strong> you can view entries but cannot delete uploads submitted by others.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
