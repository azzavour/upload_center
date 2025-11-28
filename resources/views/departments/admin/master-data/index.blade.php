@extends('layouts.app')

@section('title', 'Master Data')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-database text-primary me-2"></i>Master Data (All Departments)
            </h2>
            <p class="text-muted mb-0 mt-2">Review and export data submitted by every department.</p>
        </div>
        <div>
            <a href="{{ route('admin.master-data.duplicates') }}" class="btn btn-warning me-2">
                <i class="fas fa-exclamation-triangle me-2"></i>Check Duplicates
            </a>
            <a href="{{ route('admin.master-data.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" 
                class="btn btn-success">
                <i class="fas fa-file-export me-2"></i>Export to CSV
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filters -->
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
                <div class="col-md-3">
                    <label class="form-label small">Source Table</label>
                    <input type="text" name="source_table" class="form-control form-control-sm" 
                        value="{{ request('source_table') }}" placeholder="Table name">
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

        <!-- Data Table -->
        @if($masterData->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-database text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No data available.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th>Department</th>
                        <th>Source Table</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masterData as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>
                            <span class="badge badge-soft-info">{{ $record->department->name ?? 'N/A' }}</span>
                        </td>
                        <td><code>{{ $record->source_table }}</code></td>
                        <td>{{ $record->uploadHistory->uploader->name ?? 'N/A' }}</td>
                        <td>{{ $record->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#data-{{ $record->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="data-{{ $record->id }}">
                        <td colspan="6">
                            <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($record->data, JSON_PRETTY_PRINT) }}</pre>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $masterData->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
