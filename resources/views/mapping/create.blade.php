@extends('layouts.app')

@section('title', 'Buat Data Mapping')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-project-diagram text-primary me-2"></i>Buat Data Mapping
        </h2>
        <p class="text-muted mb-0 mt-2">Mapping kolom Excel ke kolom database untuk format: <strong>{{ $format->format_name }}</strong></p>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('mapping.store') }}" method="POST" id="mappingForm">
            @csrf
            <input type="hidden" name="excel_format_id" value="{{ $format->id }}">

            <!-- Mapping Name & Description -->
            <div class="alert alert-primary" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-tag me-2"></i>Informasi Mapping
                </h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            Nama Mapping <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="mapping_name" required class="form-control"
                            placeholder="Contoh: Mapping Data Lagu Spotify 2025">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="description" rows="2" class="form-control"
                            placeholder="Deskripsi singkat (opsional)"></textarea>
                    </div>
                </div>
            </div>

            @if(!empty($excelColumns))
            <!-- Excel Columns Detected -->
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Kolom Excel yang Terdeteksi:</strong>
                <div class="mt-2">
                    @foreach($excelColumns as $col)
                    <span class="badge bg-success me-1 mb-1">{{ $col }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Mapping Table -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>Pemetaan Kolom
                    </h6>
                    <button type="button" onclick="addMappingRow()" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Baris
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="mappingTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Kolom Excel</th>
                                    <th class="text-center" width="50"><i class="fas fa-arrow-right"></i></th>
                                    <th width="40%">Kolom Database</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="mappingBody">
                                <!-- Default row -->
                                <tr class="mapping-row">
                                    <td>
                                        <input type="text" class="form-control excel-column-input" 
                                            placeholder="Masukkan nama kolom Excel" required>
                                    </td>
                                    <td class="text-center align-middle">
                                        <i class="fas fa-long-arrow-alt-right text-muted"></i>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control db-column-input" 
                                            placeholder="Masukkan nama kolom Database" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removeMappingRow(this)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Kolom Excel harus persis sama dengan nama kolom di file Excel Anda (case-sensitive)</li>
                    <li>Kolom Database akan otomatis dinormalisasi (lowercase, underscore, no special chars)</li>
                    <li>Target table: <code>{{ $format->target_table }}</code></li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('upload.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Mapping
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addMappingRow() {
    const tbody = document.getElementById('mappingBody');
    const tr = document.createElement('tr');
    tr.className = 'mapping-row';
    tr.innerHTML = `
        <td>
            <input type="text" class="form-control excel-column-input" 
                placeholder="Masukkan nama kolom Excel" required>
        </td>
        <td class="text-center align-middle">
            <i class="fas fa-long-arrow-alt-right text-muted"></i>
        </td>
        <td>
            <input type="text" class="form-control db-column-input" 
                placeholder="Masukkan nama kolom Database" required>
        </td>
        <td class="text-center">
            <button type="button" onclick="removeMappingRow(this)" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeMappingRow(button) {
    const rows = document.querySelectorAll('.mapping-row');
    if (rows.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('Minimal harus ada satu mapping!');
    }
}

document.getElementById('mappingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const columnMapping = {};
    const rows = document.querySelectorAll('.mapping-row');
    
    rows.forEach(row => {
        const excelCol = row.querySelector('.excel-column-input').value.trim();
        const dbCol = row.querySelector('.db-column-input').value.trim();
        
        if (excelCol && dbCol) {
            columnMapping[excelCol] = dbCol;
        }
    });
    
    if (Object.keys(columnMapping).length === 0) {
        alert('Minimal isi satu mapping kolom!');
        return false;
    }
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'column_mapping';
    hiddenInput.value = JSON.stringify(columnMapping);
    this.appendChild(hiddenInput);
    
    this.submit();
});
</script>
@endpush