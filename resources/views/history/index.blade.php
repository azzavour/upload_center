@extends('layouts.app')

@section('title', 'Upload History')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-history text-primary me-2"></i>Upload History
        </h2>
        <p class="text-muted mb-0 mt-2">Daftar riwayat upload file Excel</p>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($histories->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada riwayat upload</p>
            <p class="text-muted small">Upload file pertama Anda untuk memulai</p>
            <a href="{{ route('upload.index') }}" class="btn btn-primary mt-3">
                <i class="fas fa-upload me-2"></i>Upload File Pertama
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Filename</th>
                        <th>Format</th>
                        <th>Uploaded By</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Rows</th>
                        <th>Upload Date</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $displayTimezone = config('app.display_timezone', config('app.timezone', 'UTC'));
                    @endphp
                    @foreach($histories as $history)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <i class="fas fa-file-excel text-success me-2"></i>
                            {{ $history->original_filename }}
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ $history->excelFormat->format_name }}
                            </span>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-user me-1"></i>
                                {{ $history->uploader ? $history->uploader->name : 'Unknown' }}
                            </small>
                        </td>
                        <td>
                            @if($history->upload_mode === 'replace')
                                <span class="badge bg-warning">
                                    <i class="fas fa-sync-alt me-1"></i>Replace
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-plus-circle me-1"></i>Append
                                </span>
                            @endif
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
                                <span class="badge bg-secondary">
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
                            <small>{{ $history->uploaded_at->timezone($displayTimezone)->format('d M Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('history.show', $history->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(in_array($history->status, ['pending', 'processing']))
                            <form action="{{ route('history.cancel', $history->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan proses upload ini?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-stop"></i>
                                </button>
                            </form>
                            @endif
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

@push('scripts')
<script>
    setInterval(function () {
        window.location.reload();
    }, 30000);
</script>
@endpush
