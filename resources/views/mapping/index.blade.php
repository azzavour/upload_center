@extends('layouts.app')

@section('title', 'Data Mapping')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-project-diagram text-primary me-2"></i>Data Mapping Terdaftar
        </h2>
        <p class="text-muted mb-0 mt-2">Lihat semua konfigurasi mapping yang telah dibuat</p>
    </div>

    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($mappings->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-project-diagram text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada mapping terdaftar</p>
            <p class="text-muted small">Mapping akan otomatis dibuat saat Anda upload file dengan format baru</p>
        </div>
        @else
        <div class="row">
            @foreach($mappings as $mapping)
            <div class="col-12 mb-3">
                <div class="card border">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <!-- Mapping Index & Time -->
                                <div class="mb-3">
                                    <span class="badge bg-primary fs-6 me-2">
                                        <i class="fas fa-fingerprint me-1"></i>
                                        {{ $mapping->mapping_index }}
                                    </span>
                                    <small class="text-muted">dibuat {{ $mapping->created_at->diffForHumans() }}</small>
                                </div>

                                <!-- Format Info -->
                                <div class="mb-3">
                                    <p class="mb-1 small">
                                        <i class="fas fa-table me-1"></i>
                                        <strong>Format:</strong> 
                                        <span class="text-primary">{{ $mapping->excelFormat->format_name }}</span>
                                    </p>
                                    <p class="mb-0 small text-muted">
                                        <i class="fas fa-database me-1"></i>
                                        <strong>Target Table:</strong> 
                                        <code class="bg-light px-2 py-1 rounded">{{ $mapping->excelFormat->target_table }}</code>
                                    </p>
                                </div>

                                <!-- Column Mapping -->
                                <div class="mb-2">
                                    <p class="mb-2 small fw-bold">
                                        <i class="fas fa-arrows-alt-h me-1"></i>Column Mapping:
                                    </p>
                                    <div class="bg-light rounded p-3">
                                        <div class="row g-2">
                                            @foreach($mapping->column_mapping as $excelCol => $dbCol)
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center small">
                                                    <code class="bg-white border px-2 py-1 rounded">{{ $excelCol }}</code>
                                                    <i class="fas fa-long-arrow-alt-right mx-2 text-muted"></i>
                                                    <code class="bg-info bg-opacity-25 border border-info px-2 py-1 rounded text-primary">{{ $dbCol }}</code>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Transformation Rules -->
                                @if($mapping->transformation_rules && count($mapping->transformation_rules) > 0)
                                <div class="mt-3">
                                    <p class="mb-2 small fw-bold">
                                        <i class="fas fa-magic me-1"></i>Transformation Rules:
                                    </p>
                                    <div>
                                        @foreach($mapping->transformation_rules as $field => $rule)
                                        <span class="badge bg-secondary me-1 mb-1">
                                            {{ $field }}: {{ $rule['type'] ?? 'N/A' }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="ms-3">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('mapping.show', $mapping->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </a>
                                    <form action="{{ route('mapping.destroy', $mapping->id) }}" method="POST" 
                                        onsubmit="return confirm('Yakin ingin menghapus mapping {{ $mapping->mapping_index }}? Tindakan ini tidak dapat dibatalkan!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection