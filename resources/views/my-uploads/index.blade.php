@extends('layouts.app')

@section('title', 'My Uploads')

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
                <i class="fas fa-percentage text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['success_rate'] }}%</h3>
                <small class="text-muted">Success Rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['total_failed_rows'] }}</h3>
                <small class="text-muted">Failed Rows</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-history text-primary me-2"></i>My Upload History
            </h2>
            <p class="text-muted mb-0 mt-2">
                Tracking semua upload yang telah Anda lakukan
            </p>
        </div>
        <div>
            <a href="{{ route('my-uploads.stats') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-2"></i>View Statistics
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Upload Mode</label>
                    <select name="upload_mode" class="form-select form-select-sm">
                        <option value="">All Modes</option>
                        <option value="append" {{ request('upload_mode') == 'append' ? 'selected' : '' }}>Append</option>
                        <option value="replace" {{ request('upload_mode') == 'replace' ? 'selected' : '' }}>Replace</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                        value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" 
                        value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Last Upload Info -->
        @if($stats['last_upload'])
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Last Upload:</strong> {{ $stats['last_upload']->original_filename }} 
            ({{ $stats['last_upload']->uploaded_at->diffForHumans() }})
        </div>
        @endif

        <!-- Upload Table -->
        @if($uploads->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">Anda belum melakukan upload apapun</p>
            <a href="{{ route('upload.index') }}" class="btn btn-primary mt-3">
                <i class="fas fa-upload me-2"></i>Upload File Pertama
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Date & Time</th>
                        <th>Filename</th>
                        <th>Format</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Rows</th>
                        <th>Accuracy</th>
                        <th width="100">Action</th>
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
                            <i class="fas fa-file-excel text-success me-1"></i>
                            {{ Str::limit($upload->original_filename, 30) }}
                        </td>
                        <td>
                            <span class="badge bg-info text-white small">
                                {{ $upload->excelFormat->format_name }}
                            </span>
                        </td>
                        <td>
                            @if($upload->upload_mode === 'replace')
                                <span class="badge bg-warning small">Replace</span>
                            @else
                                <span class="badge bg-success small">Append</span>
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
                                <span class="badge bg-secondary small">{{ ucfirst($upload->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">✓ {{ $upload->success_rows }}</div>
                                @if($upload->failed_rows > 0)
                                <div class="text-danger">✗ {{ $upload->failed_rows }}</div>
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
                            <a href="{{ route('history.show', $upload->id) }}" 
                                class="btn btn-sm btn-outline-primary"
                                title="View Details">
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
            {{ $uploads->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection