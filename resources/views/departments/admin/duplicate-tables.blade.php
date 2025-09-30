@extends('layouts.app')

@section('title', 'Duplicate Table Detection')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>Duplicate Table Detection
        </h2>
        <p class="text-muted mb-0 mt-2">Deteksi tabel dengan struktur sama tapi nama berbeda</p>
    </div>

    <div class="card-body">
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Apa itu Duplicate Table?</strong><br>
            Sistem mendeteksi tabel yang memiliki struktur kolom identik namun dengan nama berbeda.
            Ini bisa menyebabkan redundansi data dan memperlambat performa sistem.
        </div>

        @if(empty($duplicates))
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <p class="text-success mt-3 mb-0 fw-bold">Tidak Ada Duplikasi Terdeteksi!</p>
            <p class="text-muted">Semua tabel memiliki struktur yang unik.</p>
        </div>
        @else
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Ditemukan {{ count($duplicates) }} kemungkinan duplikasi tabel!</strong>
        </div>

        @foreach($duplicates as $index => $dup)
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0">
                    <i class="fas fa-clone me-2"></i>Duplikasi #{{ $index + 1 }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <p class="mb-1"><strong>Tabel Original:</strong></p>
                        <code class="bg-light px-3 py-2 rounded d-inline-block">{{ $dup['original_table'] }}</code>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-equals text-muted" style="font-size: 2rem; margin-top: 10px;"></i>
                    </div>
                    <div class="col-md-5">
                        <p class="mb-1"><strong>Tabel Duplicate:</strong></p>
                        <code class="bg-light px-3 py-2 rounded d-inline-block">{{ $dup['duplicate_table'] }}</code>
                    </div>
                </div>

                <hr>

                <p class="mb-2"><strong>Struktur Kolom yang Sama:</strong></p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($dup['columns'] as $col)
                        <span class="badge bg-secondary">{{ $col }}</span>
                    @endforeach
                </div>

                <div class="alert alert-light mt-3 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Rekomendasi:</strong> Pertimbangkan untuk menggunakan satu tabel saja atau 
                    memberikan nama kolom yang berbeda jika memang berbeda fungsi.
                </div>
            </div>
        </div>
        @endforeach

        <div class="alert alert-primary mt-4" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-tools me-2"></i>Langkah Penanganan
            </h6>
            <ol class="mb-0">
                <li>Review apakah tabel-tabel tersebut memang perlu dipisah</li>
                <li>Jika tidak perlu, gunakan mapping ke satu tabel yang sama</li>
                <li>Jika perlu dipisah, tambahkan kolom identifikasi unik</li>
                <li>Lakukan migrasi data jika diperlukan</li>
            </ol>
        </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('admin.master-data.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Master Data
            </a>
        </div>
    </div>
</div>
@endsection