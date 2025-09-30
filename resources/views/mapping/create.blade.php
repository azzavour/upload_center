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
                            placeholder="Contoh: Mapping Keuangan Tahun 2025">
                        <small class="text-muted">Berikan nama yang deskriptif untuk mapping ini</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="description" rows="2" class="form-control"
                            placeholder="Deskripsi singkat (opsional)"></textarea>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                Petakan kolom dari file Excel Anda ke kolom database yang sesuai. 
                <strong>1 Mapping = 1 Tabel Database ({{ $format->target_table }})</strong>
            </div>

            @if(!empty($excelColumns))
            <!-- Excel Columns Detected -->
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Kolom Excel yang Terdeteksi:</strong>
                <div class="mt-2">
                    @foreach($excelColumns as $col)
                    <span class="badge bg-success me-1">{{ $col }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Mapping Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Kolom Excel</th>
                            <th class="text-center" width="50"><i class="fas fa-arrow-right"></i></th>
                            <th>Kolom Database ({{ $format->target_table }})</th>
                            <th>Transformasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($format->expected_columns as $dbCol)
                        @php
                            // Auto-match helper
                            $matchedExcel = '';
                            $normalized = strtolower(str_replace('_', ' ', $dbCol));
                            foreach ($excelColumns as $excel) {
                                $excelNorm = strtolower(trim($excel));
                                if ($excelNorm === $normalized || 
                                    str_replace(' ', '', $excelNorm) === str_replace(' ', '', $normalized)) {
                                    $matchedExcel = $excel;
                                    break;
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <input type="text" 
                                    class="form-control excel-column-input" 
                                    placeholder="Contoh: {{ ucwords(str_replace('_', ' ', $dbCol)) }}"
                                    id="excel_{{ $dbCol }}"
                                    value="{{ $matchedExcel }}">
                            </td>
                            <td class="text-center align-middle">
                                <i class="fas fa-long-arrow-alt-right text-muted"></i>
                            </td>
                            <td class="align-middle">
                                <code class="bg-light p-2 rounded">{{ $dbCol }}</code>
                            </td>
                            <td>
                                <select name="transformation_rules[{{ $dbCol }}][type]" class="form-select form-select-sm">
                                    <option value="">Tidak Ada</option>
                                    <option value="trim">Trim (Hapus Spasi)</option>
                                    <option value="uppercase">UPPERCASE</option>
                                    <option value="lowercase">lowercase</option>
                                    <option value="date_format">Format Tanggal</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Example Box -->
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Contoh Mapping
                </h6>
                <p class="mb-1"><strong>Jika Excel Anda memiliki kolom:</strong> "id_track", "nama_lagu", "nama_artis"</p>
                <p class="mb-0"><strong>Maka isi:</strong></p>
                <ul class="mb-0">
                    <li>Untuk track_id: ketik "id_track"</li>
                    <li>Untuk track_name: ketik "nama_lagu"</li>
                    <li>Untuk artist_name: ketik "nama_artis"</li>
                </ul>
                <p class="mb-0 mt-2 fw-bold">⚠️ Kosongkan kolom yang tidak ada di Excel Anda</p>
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
document.getElementById('mappingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const columnMapping = {};
    const inputs = document.querySelectorAll('.excel-column-input');
    
    inputs.forEach(input => {
        const dbColumn = input.id.replace('excel_', '');
        const excelColumn = input.value.trim();
        
        if (excelColumn) {
            columnMapping[excelColumn] = dbColumn;
        }
    });
    
    if (Object.keys(columnMapping).length === 0) {
        alert('Minimal isi satu mapping kolom!');
        return false;
    }
    
    console.log('Column Mapping:', columnMapping);
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'column_mapping';
    hiddenInput.value = JSON.stringify(columnMapping);
    this.appendChild(hiddenInput);
    
    this.submit();
});
</script>
@endpush