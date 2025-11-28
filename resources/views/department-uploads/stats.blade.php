@extends('layouts.app')

@section('title', 'Department Statistics')

@section('content')
<div class="mb-3">
    <a href="{{ route('department-uploads.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Department Uploads
    </a>
</div>

<!-- Department Info -->
<div class="alert alert-primary" role="alert">
    <h5 class="alert-heading">
        <i class="fas fa-building me-2"></i>{{ $user->department->name }} Statistics
    </h5>
    <p class="mb-0">Upload statistics for the entire department.</p>
</div>

<!-- Statistics Overview -->
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
                <h3 class="mt-2 mb-0">{{ $stats['data_accuracy'] }}%</h3>
                <small class="text-muted">Data Accuracy</small>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Upload Trend (Last 6 Months)
        </h5>
    </div>
    <div class="card-body">
        @if($monthlyTrend->isEmpty())
        <p class="text-muted text-center">No data available</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th class="text-center">Total Uploads</th>
                        <th class="text-center">Success Rows</th>
                        <th class="text-center">Failed Rows</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyTrend as $trend)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($trend->month . '-01')->format('F Y') }}</td>
                        <td class="text-center">
                            <span class="badge badge-soft-primary">{{ $trend->total_uploads }}</span>
                        </td>
                        <td class="text-center text-success">{{ number_format($trend->total_success) }}</td>
                        <td class="text-center text-danger">{{ number_format($trend->total_failed) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Upload by Format -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-file-excel me-2"></i>Upload by Format
        </h5>
    </div>
    <div class="card-body">
        @if($uploadsByFormat->isEmpty())
        <p class="text-muted text-center">No data available</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Format Name</th>
                        <th class="text-center">Total Uploads</th>
                        <th class="text-center">Total Rows</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploadsByFormat as $format)
                    <tr>
                        <td>
                            <i class="fas fa-file-excel text-success me-2"></i>
                            {{ $format->format_name }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-soft-info">{{ $format->total_uploads }}</span>
                        </td>
                        <td class="text-center">{{ number_format($format->total_rows) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Highlight: Uploads grouped by user -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>Top Uploaders in Department
        </h5>
    </div>
    <div class="card-body">
        @if($uploadsByUser->isEmpty())
        <p class="text-muted text-center">No data available</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th class="text-center">Total Uploads</th>
                        <th class="text-center">Total Rows</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploadsByUser as $userStat)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            <strong>{{ $userStat->name }}</strong>
                        </td>
                        <td class="text-muted small">{{ $userStat->email }}</td>
                        <td class="text-center">
                            <span class="badge badge-soft-primary">{{ $userStat->total_uploads }}</span>
                        </td>
                            <td class="text-center">{{ number_format($userStat->total_rows) }}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
