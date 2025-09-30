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
                    placeholder="Contoh: Format Data Produk 2025">
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
                    <!-- Default columns - CONTOH UMUM -->
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Kode Produk">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Nama Produk">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Kategori">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Harga">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" value="Stok">
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
                <input type="text" name="target_table" required class="form-control" value="produk"
                    placeholder="Nama tabel di database (contoh: produk, karyawan, penjualan)">
                @error('target_table')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Tabel akan otomatis dibuat dengan prefix department: <code>dept_[kode_dept]_[nama_tabel]</code>
                </small>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb me-2"></i>Panduan Pengisian
                </h6>
                <ul class="mb-0 small">
                    <li><strong>Nama Format:</strong> Berikan nama yang deskriptif, contoh: "Format Laporan Penjualan Q1 2025"</li>
                    <li><strong>Kolom:</strong> Isi sesuai header di Excel Anda, contoh: Tanggal, Nama Customer, Total Pembelian</li>
                    <li><strong>Target Table:</strong> Nama tabel untuk menyimpan data, contoh: penjualan, karyawan, produk</li>
                    <li><strong>Otomatis:</strong> Sistem akan membuat tabel dengan prefix department Anda</li>
                </ul>
            </div>

            <!-- Examples Box -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title text-primary mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>Contoh Format
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Format Data Produk</strong>
                                <div class="small text-muted">
                                    Kolom: Kode Produk, Nama Produk, Kategori, Harga, Stok<br>
                                    Target: <code>produk</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Format Data Karyawan</strong>
                                <div class="small text-muted">
                                    Kolom: NIK, Nama, Jabatan, Departemen, Gaji<br>
                                    Target: <code>karyawan</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Format Laporan Penjualan</strong>
                                <div class="small text-muted">
                                    Kolom: Tanggal, No Invoice, Customer, Total, Status<br>
                                    Target: <code>penjualan</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Format Inventory</strong>
                                <div class="small text-muted">
                                    Kolom: SKU, Nama Barang, Lokasi, Qty, Satuan<br>
                                    Target: <code>inventory</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
            placeholder="Nama kolom (contoh: Tanggal, Jumlah, Keterangan)">
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