@extends('layouts.app')

@section('title', 'Tambah Format Baru')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-plus-circle text-primary me-2"></i>Tambah Format Excel Baru
        </h2>
        <p class="text-muted mb-0 mt-2">Daftarkan format Excel baru untuk sistem upload</p>
    </div>

    <div class="card-body">
        <form action="{{ route('formats.store') }}" method="POST">
            @csrf

            <!-- Format Name -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Nama Format <span class="text-danger">*</span>
                </label>
                <input type="text" name="format_name" required class="form-control"
                    placeholder="Contoh: Music Tracks Format Standard">
                @error('format_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control"
                    placeholder="Deskripsi singkat tentang format ini"></textarea>
            </div>

            <!-- Expected Columns -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Kolom yang Diharapkan <span class="text-danger">*</span>
                </label>
                <div id="columns-container" class="mb-3">
                    <!-- Default columns -->
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Track ID">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Track Name">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Artist ID">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Artist Name">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addColumn()" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-plus me-2"></i>Tambah Kolom
                </button>
            </div>

            <!-- Target Table -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Target Table <span class="text-danger">*</span>
                </label>
                <input type="text" name="target_table" required class="form-control" value="tracks"
                    placeholder="Nama tabel di database (contoh: tracks)">
                @error('target_table')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Pastikan nama kolom sesuai dengan header di file Excel Anda. 
                Target table harus sudah ada di database PostgreSQL.
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('formats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Format
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addColumn() {
    const container = document.getElementById('columns-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2 column-row';
    div.innerHTML = `
        <input type="text" name="expected_columns[]" required class="form-control"
            placeholder="Nama kolom (contoh: Album Name)">
        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeColumn(button) {
    const rows = document.querySelectorAll('.column-row');
    if (rows.length > 1) {
        button.closest('.column-row').remove();
    } else {
        alert('Minimal harus ada satu kolom!');
    }
}
</script>
@endpush