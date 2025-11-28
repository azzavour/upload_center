@extends('layouts.app')

@section('title', 'All Uploads')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>All Uploads (Admin View)
        </h2>
        <p class="text-muted mb-0 mt-2">Review every upload submitted across all departments.</p>
    </div>

    <div class="card-body">
        @if($histories->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No uploads have been recorded yet.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Department</th>
                        <th>Filename</th>
                        <th>Uploaded By</th>
                        <th>Format</th>
                        <th>Status</th>
                        <th>Rows</th>
                        <th>Date</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $history)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="badge badge-soft-primary">
                                {{ $history->department->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <i class="fas fa-file-excel text-success me-2"></i>
                            {{ Str::limit($history->original_filename, 30) }}
                        </td>
                        <td>
                            <i class="fas fa-user me-1"></i>
                            {{ $history->uploader->name ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="badge badge-soft-info">
                                {{ $history->excelFormat->format_name }}
                            </span>
                        </td>
                        <td>
                            @if($history->status === 'completed')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Completed
                                </span>
                            @elseif($history->status === 'processing')
                                <span class="badge bg-warning">
                                    <i class="fas fa-spinner me-1"></i>Processing
                                </span>
                            @elseif($history->status === 'failed')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Failed
                                </span>
                            @else
                                <span class="badge badge-soft-neutral">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>
                           @endif
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">
                                    <i class="fas fa-check me-1"></i>{{ $history->success_rows }}
                                </div>
                                @if($history->failed_rows > 0)
                                <div class="text-danger">
                                    <i class="fas fa-times me-1"></i>{{ $history->failed_rows }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <small>{{ $history->uploaded_at->format('d M Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('history.show', $history->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
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
