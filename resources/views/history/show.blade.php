@extends('layouts.app')

@section('title', 'Detail Upload')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-file-alt text-primary me-2"></i>Detail Upload
        </h2>
        <p class="text-muted mb-0 mt-2">{{ $history->original_filename }}</p>
    </div>

    <div class="card-body">
        <a href="{{ route('history.index') }}" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>

        <!-- Success Message -->
        @if($history->status === 'completed')
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Upload Berhasil!</strong>
            <p class="mb-0">File telah berhasil diproses dan data telah dimasukkan ke database.</p>
        </div>
        @elseif($history->status === 'failed')
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Upload Gagal!</strong>
            <p class="mb-0">Terjadi kesalahan saat memproses file.</p>
        </div>
        @endif

        <!-- File Info -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi File
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Nama File Asli</strong>
                        <p class="mb-0">{{ $history->original_filename }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Nama File Tersimpan</strong>
                        <p class="mb-0">{{ $history->stored_filename }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Waktu Upload</strong>
                        <p class="mb-0">{{ $history->uploaded_at->format('d M Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Format & Mapping -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Format & Mapping
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Format Excel</strong>
                    <p class="mb-0">{{ $history->excelFormat->format_name }}</p>
                </div>
                @if($history->mappingConfiguration)
                <div class="mb-3">
                    <strong>Mapping Configuration</strong>
                    <p class="mb-0">
                        <span class="badge bg-primary">{{ $history->mappingConfiguration->mapping_index }}</span>
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Statistik Upload
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h3 class="text-primary mb-0">{{ $history->total_rows }}</h3>
                            <small class="text-muted">Total Baris</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h3 class="text-success mb-0">{{ $history->success_rows }}</h3>
                            <small class="text-muted">Berhasil</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h3 class="text-danger mb-0">{{ $history->failed_rows }}</h3>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>
                </div>

                @if($history->total_rows > 0)
                <div class="mt-3">
                    <strong>Success Rate</strong>
                    <div class="progress" style="height: 25px;">
                        @php
                            $successRate = ($history->success_rows / $history->total_rows) * 100;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: {{ $successRate }}%"
                            aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($successRate, 1) }}%
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Error Details -->
        @if($history->failed_rows > 0 && $history->error_details)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Detail Error ({{ count($history->error_details) }} error)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Baris</th>
                                <th>Error Message</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history->error_details as $error)
                            <tr>
                                <td class="text-center">{{ $error['row'] ?? 'N/A' }}</td>
                                <td>
                                    <small class="text-danger">{{ $error['error'] ?? 'Unknown error' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#data-{{ $loop->index }}">
                                        <i class="fas fa-eye me-1"></i>Lihat Data
                                    </button>
                                    <div class="collapse mt-2" id="data-{{ $loop->index }}">
                                        <pre class="bg-light p-2 rounded small mb-0">{{ json_encode($error['data'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Column Mapping Used -->
        @if($history->mappingConfiguration)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-arrows-alt-h me-2"></i>Column Mapping yang Digunakan
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($history->mappingConfiguration->column_mapping as $excelCol => $dbCol)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center small">
                            <code class="bg-light border px-2 py-1 rounded">{{ $excelCol }}</code>
                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                            <code class="bg-info bg-opacity-25 border border-info px-2 py-1 rounded">{{ $dbCol }}</code>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection