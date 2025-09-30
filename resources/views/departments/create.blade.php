@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-plus-circle text-primary me-2"></i>Tambah Department Baru
        </h2>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.departments.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-bold">
                    Department Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" required class="form-control @error('name') is-invalid @enderror"
                    placeholder="Contoh: Finance Department" value="{{ old('name') }}">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Department Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                    placeholder="Contoh: FIN (otomatis jika kosong)" value="{{ old('code') }}">
                @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Kosongkan untuk generate otomatis dari nama</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" rows="3" class="form-control"
                    placeholder="Deskripsi singkat tentang department ini">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Department
                </button>
            </div>
        </form>
    </div>
</div>
@endsection