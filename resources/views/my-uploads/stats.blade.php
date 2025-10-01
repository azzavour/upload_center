@extends('layouts.app')

@section('title', 'My Upload Statistics')

@section('content')
<div class="mb-3">
    <a href="{{ route('my-uploads.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to My Uploads
    </a>
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
                <i class="fas fa-percentage text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['data_accuracy'] }}%</h3>
                <small class="text-muted">Data Accuracy</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-chart-line text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-0">{{ $stats['success_rate'] }}%</h3>
                <small class="text-muted">Success Rate</small>
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
                            <span class="badge bg-primary">{{ $trend->total_uploads }}</span>
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
<div class="card shadow-sm">
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
                            <span class="badge bg-info">{{ $format->total_uploads }}</span>
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
@endsection