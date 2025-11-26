@extends('layouts.app')

@section('title', 'Upload Data')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-upload text-primary me-2"></i>Upload Data Excel
        </h2>
        <p class="text-muted mb-0 mt-2">Upload file Excel untuk data musik/tracks</p>
    </div>

    <div class="card-body">
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            
            <!-- Format Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-list-alt me-1"></i>Pilih Format Excel
                </label>
                <select name="format_id" id="format_id" required class="form-select">
                    <option value="">-- Pilih Format --</option>
                    @foreach($formats as $format)
                        <option value="{{ $format->id }}">
                            {{ $format->format_name }} ({{ $format->format_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- File Upload -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-file-excel me-1"></i>Pilih File Excel
                </label>
                <div class="border border-2 border-dashed rounded p-5 text-center" style="border-color: #dee2e6;">
                    <i class="fas fa-cloud-upload-alt text-muted" style="font-size: 3rem;"></i>
                    <div class="mt-3">
                        <label for="file-upload" class="btn btn-primary btn-sm">
                            <i class="fas fa-folder-open me-2"></i>Pilih File
                        </label>
                        <input id="file-upload" name="file" type="file" class="d-none" required>
                        <p class="text-muted mb-0 mt-2">atau drag and drop</p>
                    </div>
                    <small class="text-muted">XLSX, XLS, CSV hingga 40MB</small>
                    <p id="file-name" class="text-success fw-bold mt-2"></p>
                </div>
            </div>

            <!-- Upload Mode Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-cog me-1"></i>Mode Upload
                </label>
                <div class="card">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="upload_mode" id="mode_append" value="append" checked>
                            <label class="form-check-label" for="mode_append">
                                <strong><i class="fas fa-plus-circle text-success me-2"></i>Append (Tambahkan Data)</strong>
                                <p class="text-muted small mb-0 ms-4">Data baru akan ditambahkan ke data yang sudah ada. Data lama tidak akan dihapus.</p>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="upload_mode" id="mode_replace" value="replace">
                            <label class="form-check-label" for="mode_replace">
                                <strong><i class="fas fa-sync-alt text-warning me-2"></i>Replace (Ganti Data)</strong>
                                <p class="text-muted small mb-0 ms-4">Data lama dari department Anda akan dihapus dan diganti dengan data baru.</p>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-2 d-none" id="replace-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong> Mode Replace akan menghapus semua data sebelumnya dari department Anda untuk format ini.
                </div>
            </div>

            <!-- Warning for New Format -->
            <div id="mapping-section" class="mb-4 d-none">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Format Baru Terdeteksi!</strong> 
                    Kolom Excel tidak sesuai dengan format yang terdaftar. Silakan lakukan mapping kolom.
                </div>
                <input type="hidden" name="mapping_id" id="mapping_id">
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" id="checkBtn" class="btn btn-outline-secondary">
                    <i class="fas fa-search me-2"></i>Cek Format
                </button>
                
                <button type="submit" id="uploadBtn" disabled class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload & Process
                </button>
            </div>
        </form>

        <!-- Preview Section -->
        <div id="preview-section" class="mt-4 d-none">
            <h5 class="mb-3">
                <i class="fas fa-table me-2"></i>Pratinjau & Analisis Kolom
            </h5>
            <p class="text-muted small">
                Berikut adalah 3 baris pertama dari file Anda. Periksa status kolom untuk memastikan data akan diimpor dengan benar.
            </p>

            <div id="preview-container" class="table-responsive border rounded">
                <!-- Table will be inserted by JavaScript -->
            </div>

            <div class="mt-3">
                <span class="me-3">
                    <span class="badge bg-success">●</span> Kolom Terpetakan
                </span>
                <span>
                    <span class="badge bg-warning">●</span> Kolom Diabaikan
                </span>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="mt-4 text-center d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Memproses file...</p>
        </div>
    </div>
</div>

<!-- Guide Box -->
<div class="card mt-4 border-primary">
    <div class="card-body bg-light">
        <h5 class="card-title text-primary">
            <i class="fas fa-lightbulb me-2"></i>Panduan Upload
        </h5>
        <ul class="mb-0 small">
            <li><strong>Pastikan file Excel memiliki header di baris pertama</strong></li>
            <li><strong>Format file yang didukung:</strong> XLSX, XLS, CSV (maksimal 40MB)</li>
            <li><strong>Pilih format yang sesuai</strong> dengan data yang akan diupload</li>
            <li><strong>Mode Upload:</strong>
                <ul class="mt-1">
                    <li><strong>Append (Tambahkan Data):</strong> Data baru ditambahkan ke data yang sudah ada. Data lama tetap tersimpan.</li>
                    <li><strong>Replace (Ganti Data):</strong> Data lama dari department Anda akan dihapus dan diganti dengan data baru.</li>
                </ul>
            </li>
            <li><strong>Sistem akan otomatis mendeteksi format file Anda:</strong>
                <ul class="mt-1">
                    <li>Jika kolom Excel <strong>sama persis</strong> dengan format yang terdaftar → upload langsung</li>
                    <li>Jika kolom Excel <strong>berbeda</strong> → sistem akan meminta Anda membuat mapping</li>
                </ul>
            </li>
            <li><strong>Mapping:</strong> Jika kolom Excel berbeda dari format database, Anda perlu memetakan kolom Excel ke kolom database yang sesuai</li>
            <li><strong>File CSV:</strong> Pastikan encoding UTF-8 untuk menghindari karakter aneh</li>
            <li><strong>Setelah upload berhasil,</strong> Anda dapat melihat hasilnya di menu <strong>Department Uploads</strong></li>
        </ul>
        
        <div class="alert alert-info mt-3 mb-0">
            <strong><i class="fas fa-info-circle me-2"></i>Tips:</strong>
            <ul class="mb-0 mt-2 small">
                <li>Gunakan tombol <strong>"Cek Format"</strong> untuk melihat pratinjau data sebelum upload</li>
                <li>Pastikan data sudah benar sebelum klik <strong>"Upload & Process"</strong></li>
                <li>Jika ada error, cek detail error di halaman <strong>History</strong></li>
                <li>Semua user di department Anda dapat melihat file yang Anda upload di <strong>Department Uploads</strong></li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Validasi file extension secara manual (lebih fleksibel)
function isValidFileType(fileName) {
    const validExtensions = ['.xlsx', '.xls', '.csv'];
    const extension = fileName.toLowerCase().substring(fileName.lastIndexOf('.'));
    return validExtensions.includes(extension);
}

// Show/hide warning when replace mode is selected
document.querySelectorAll('input[name="upload_mode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const warning = document.getElementById('replace-warning');
        if (this.value === 'replace') {
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    });
});

document.getElementById('file-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileNameDisplay = document.getElementById('file-name');
    
    if (file) {
        if (!isValidFileType(file.name)) {
            alert('File harus berformat XLSX, XLS, atau CSV');
            this.value = '';
            fileNameDisplay.textContent = '';
            return;
        }
        
        if (file.size > 40 * 1024 * 1024) {
            alert('Ukuran file maksimal 40MB');
            this.value = '';
            fileNameDisplay.textContent = '';
            return;
        }
        
        fileNameDisplay.textContent = '✓ ' + file.name;
    }
});

document.getElementById('checkBtn').addEventListener('click', async function() {
    const formData = new FormData();
    const fileInput = document.getElementById('file-upload');
    const formatId = document.getElementById('format_id').value;
    
    // Hide preview and reset upload button
    document.getElementById('preview-section').classList.add('d-none');
    document.getElementById('uploadBtn').disabled = true;

    if (!fileInput.files[0] || !formatId) {
        alert('Pilih format dan file terlebih dahulu!');
        return;
    }
    
    // Validasi file type
    if (!isValidFileType(fileInput.files[0].name)) {
        alert('File harus berformat XLSX, XLS, atau CSV');
        return;
    }
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memeriksa...';
    
    formData.append('file', fileInput.files[0]);
    formData.append('format_id', formatId);
    formData.append('_token', '{{ csrf_token() }}');
    
    try {
        const response = await fetch('{{ route("upload.check") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-search me-2"></i>Cek Format';
        
        if (!response.ok) {
            alert('Error: ' + (result.message || 'Terjadi kesalahan pada server.'));
            return;
        }
        
        // Display Preview
        if (result.preview) {
            const previewSection = document.getElementById('preview-section');
            const container = document.getElementById('preview-container');
            container.innerHTML = '';

            const table = document.createElement('table');
            table.className = 'table table-sm table-bordered mb-0';

            // Create Header
            const thead = document.createElement('thead');
            thead.className = 'table-light';
            const headerRow = document.createElement('tr');
            
            result.preview.headers.forEach(header => {
                const th = document.createElement('th');
                th.className = 'text-nowrap small';
                
                let badgeClass = '';
                let statusIcon = '';
                if (header.status === 'mapped') {
                    badgeClass = 'bg-success';
                    statusIcon = `<span class="badge bg-success ms-2" title="Akan diimpor ke: ${header.mapped_to}">✓</span>`;
                } else if (header.status === 'ignored') {
                    badgeClass = 'bg-warning';
                    statusIcon = `<span class="badge bg-warning ms-2" title="Kolom ini akan diabaikan">⚠</span>`;
                }
                
                th.innerHTML = `${header.name}${statusIcon}`;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Create Body
            const tbody = document.createElement('tbody');
            result.preview.data.forEach(rowData => {
                const tr = document.createElement('tr');
                for (let i = 0; i < result.preview.headers.length; i++) {
                    const td = document.createElement('td');
                    td.className = 'small';
                    td.textContent = rowData[i] || '';
                    tr.appendChild(td);
                }
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            container.appendChild(table);
            previewSection.classList.remove('d-none');
        }
        
        if (result.is_new_format) {
            if (confirm(result.message + ' Lanjutkan?')) {
                window.location.href = result.redirect;
            }
        } else {
            alert(result.message);
            
            if (result.has_mapping && result.mapping_id) {
                document.getElementById('mapping_id').value = result.mapping_id;
            }
            
            if (result.can_proceed) {
                document.getElementById('uploadBtn').disabled = false;
            }
        }
    } catch (error) {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-search me-2"></i>Cek Format';
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memeriksa format. Silakan coba lagi.');
    }
});

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('file-upload');
    
    // Validasi file type sebelum submit
    if (!isValidFileType(fileInput.files[0].name)) {
        alert('File harus berformat XLSX, XLS, atau CSV');
        return;
    }
    
    const uploadBtn = document.getElementById('uploadBtn');
    const formData = new FormData(this);
    
    document.getElementById('loading').classList.remove('d-none');
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
    
    try {
        const response = await fetch('{{ route("upload.process") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        });
        
        if (response.ok) {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const result = await response.json();
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            }
        } else {
            const result = await response.json();
            throw new Error(result.message || 'Upload gagal');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
        document.getElementById('loading').classList.add('d-none');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload & Process';
    }
});
</script>
@endpush
