@extends('layouts.app')

@section('title', 'Excel Formats')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-file-excel text-success me-2"></i>Format Excel Terdaftar
            </h2>
            <p class="text-muted mb-0 mt-2">Kelola format Excel yang dapat digunakan untuk upload</p>
        </div>
        <a href="{{ route('formats.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Format Baru
        </a>
    </div>

    <div class="card-body">
        @if($formats->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada format terdaftar</p>
            <a href="{{ route('formats.create') }}" class="btn btn-primary mt-3">
                <i class="fas fa-plus me-2"></i>Tambah Format Pertama
            </a>
        </div>
        @else
        <div class="row">
            @foreach($formats as $format)
            <div class="col-md-6 mb-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-table text-primary fs-3 me-3"></i>
                                    <div>
                                        <h5 class="mb-0">{{ $format->format_name }}</h5>
                                        <small class="text-muted">
                                            <code class="bg-light px-2 py-1 rounded">{{ $format->format_code }}</code>
                                        </small>
                                    </div>
                                </div>
                                
                                @if($format->description)
                                <p class="text-muted small">{{ $format->description }}</p>
                                @endif

                                <div class="mb-3">
                                    <strong class="small">
                                        <i class="fas fa-columns me-1"></i>Kolom yang Diharapkan:
                                    </strong>
                                    <div class="mt-2">
                                        @foreach($format->expected_columns as $column)
                                        <span class="badge bg-primary me-1 mb-1">{{ $column }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="small text-muted">
                                    <i class="fas fa-database me-1"></i>
                                    Target Table: <code class="bg-light px-2 py-1 rounded">{{ $format->target_table }}</code>
                                </div>
                            </div>
                            
                            <div>
                                @if($format->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Aktif
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle me-1"></i>Nonaktif
                                </span>
                                @endif
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