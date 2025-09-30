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
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> 
                    Format yang diharapkan: Track ID, Track Name, Artist ID, Artist Name, Album Name, Genre, Release Date, Track Price, Collection Price, Country
                </div>
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
                    <small class="text-muted">XLSX, XLS, CSV hingga 10MB</small>
                    <p id="file-name" class="text-success fw-bold mt-2"></p>
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
            <li>Pastikan file Excel memiliki header di baris pertama</li>
            <li>Kolom yang wajib ada: Track ID, Track Name, Artist Name</li>
            <li>Format tanggal: YYYY-MM-DD atau DD/MM/YYYY</li>
            <li>Harga gunakan format angka tanpa simbol mata uang</li>
            <li>Jika kolom berbeda, sistem akan meminta Anda melakukan mapping</li>
            <li><strong>File CSV juga didukung!</strong> Pastikan encoding UTF-8</li>
        </ul>
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
        
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10MB');
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