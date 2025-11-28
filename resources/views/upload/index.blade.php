@extends('layouts.app')

@section('title', 'Upload Data')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-upload text-primary me-2"></i>Upload Excel Data
        </h2>
        <p class="text-muted mb-0 mt-2">Upload Excel files to distribute structured data securely.</p>
    </div>

    <div class="card-body">
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            
            <!-- Format Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-list-alt me-1"></i>Select Excel Format
                </label>
                <select name="format_id" id="format_id" required class="form-select">
                    <option value="">-- Select Format --</option>
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
                    <i class="fas fa-file-excel me-1"></i>Select Excel File
                </label>
                <div class="dropzone">
                    <i class="fas fa-cloud-upload-alt text-primary" style="font-size: 3rem;"></i>
                    <div class="mt-3">
                        <label for="file-upload" class="btn btn-primary btn-sm">
                            <i class="fas fa-folder-open me-2"></i>Choose File
                        </label>
                        <input id="file-upload" name="file" type="file" class="d-none" required>
                        <p class="text-muted mb-0 mt-2">or drag and drop</p>
                    </div>
                    <small class="text-muted">XLSX, XLS, or CSV files up to 40MB</small>
                    <p id="file-name" class="text-success fw-bold mt-2"></p>
                </div>
            </div>

            <!-- Upload Mode Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-cog me-1"></i>Upload Mode
                </label>
                <div class="card">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="upload_mode" id="mode_append" value="append" checked>
                            <label class="form-check-label" for="mode_append">
                                <strong><i class="fas fa-plus-circle text-success me-2"></i>Append (Add Data)</strong>
                                <p class="text-muted small mb-0 ms-4">New rows are appended to the existing dataset. Previous data remains intact.</p>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="upload_mode" id="mode_replace" value="replace">
                            <label class="form-check-label" for="mode_replace">
                                <strong><i class="fas fa-sync-alt text-warning me-2"></i>Replace (Overwrite Data)</strong>
                                <p class="text-muted small mb-0 ms-4">Existing data for your department will be purged and replaced with this upload.</p>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-2 d-none" id="replace-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention!</strong> Replace mode removes every prior record for this format in your department.
                </div>
            </div>

            <!-- Warning for New Format -->
            <div id="mapping-section" class="mb-4 d-none">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>New Format Detected!</strong> 
                    Excel columns differ from the registered schema. Please map the columns before continuing.
                </div>
                <input type="hidden" name="mapping_id" id="mapping_id">
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" id="checkBtn" class="btn btn-outline-secondary">
                    <i class="fas fa-search me-2"></i>Check Format
                </button>
                
                <button type="submit" id="uploadBtn" disabled class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload & Process
                </button>
            </div>
        </form>

        <!-- Preview Section -->
        <div id="preview-section" class="mt-4 d-none">
            <h5 class="mb-3">
                <i class="fas fa-table me-2"></i>Preview & Column Analysis
            </h5>
            <p class="text-muted small">
                The first three rows of your file appear below. Verify every column status to ensure it imports correctly.
            </p>

            <div id="preview-container" class="table-responsive border rounded">
                <!-- Table will be inserted by JavaScript -->
            </div>

            <div class="mt-3">
                <span class="me-3">
                <span class="badge badge-soft-success">OK</span> Column mapped
            </span>
            <span>
                <span class="badge badge-soft-warning">!</span> Column ignored
            </span>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="mt-4 text-center d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Processing file...</p>
        </div>
    </div>
</div>

<!-- Guide Box -->
<div class="card mt-4 border-primary">
    <div class="card-body bg-light">
        <h5 class="card-title text-primary">
            <i class="fas fa-lightbulb me-2"></i>Upload Guide
        </h5>
        <ul class="mb-0 small">
            <li><strong>Ensure the Excel file includes headers on the first row.</strong></li>
            <li><strong>Supported file types:</strong> XLSX, XLS, or CSV (maximum 40MB).</li>
            <li><strong>Select the format</strong> that matches the dataset you will upload.</li>
            <li><strong>Upload modes:</strong>
                <ul class="mt-1">
                    <li><strong>Append (Add Data):</strong> Adds new rows while retaining existing information.</li>
                    <li><strong>Replace (Overwrite Data):</strong> Deletes existing rows for your department and writes the new dataset.</li>
                </ul>
            </li>
            <li><strong>The system automatically detects your file format:</strong>
                <ul class="mt-1">
                    <li>If the Excel columns <strong>match exactly,</strong> the upload proceeds immediately.</li>
                    <li>If the Excel columns <strong>differ,</strong> the system will prompt you to create a mapping.</li>
                </ul>
            </li>
            <li><strong>Mapping:</strong> If the Excel columns differ from the database, map each column to the correct destination.</li>
            <li><strong>CSV files:</strong> Use UTF-8 encoding to avoid corrupted characters.</li>
            <li><strong>After a successful upload,</strong> review the import under <strong>Department Uploads</strong>.</li>
        </ul>
        
        <div class="alert alert-info mt-3 mb-0">
            <strong><i class="fas fa-info-circle me-2"></i>Tips:</strong>
            <ul class="mb-0 mt-2 small">
                <li>Use the <strong>"Check Format"</strong> button to preview data prior to uploading.</li>
                <li>Confirm the dataset is accurate before pressing <strong>"Upload & Process"</strong>.</li>
                <li>If errors occur, review the detailed log on the <strong>History</strong> page.</li>
                <li>Every user in your department can review uploaded files via <strong>Department Uploads</strong>.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Validate file extensions manually for greater flexibility
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
            alert('File must be in XLSX, XLS, or CSV format.');
            this.value = '';
            fileNameDisplay.textContent = '';
            return;
        }
        
        if (file.size > 40 * 1024 * 1024) {
            alert('Maximum file size is 40MB.');
            this.value = '';
            fileNameDisplay.textContent = '';
            return;
        }
        
        fileNameDisplay.textContent = 'Selected file: ' + file.name;
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
        alert('Please choose both a format and a file first.');
        return;
    }
    
    // Validate file type
    if (!isValidFileType(fileInput.files[0].name)) {
        alert('File must be in XLSX, XLS, or CSV format.');
        return;
    }
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
    
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
        this.innerHTML = '<i class="fas fa-search me-2"></i>Check Format';
        
        if (!response.ok) {
            alert('Error: ' + (result.message || 'A server error occurred.'));
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
                
                let statusIcon = '';
                if (header.status === 'mapped') {
                    statusIcon = `<span class="badge badge-soft-success ms-2" title="Will be imported to: ${header.mapped_to}"><i class="fas fa-check"></i></span>`;
                } else if (header.status === 'ignored') {
                    statusIcon = `<span class="badge badge-soft-warning ms-2" title="This column will be ignored"><i class="fas fa-ban"></i></span>`;
                }
                
                th.innerHTML = `${header.name}${statusIcon}`;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Create Body
            const tbody = document.createElement('tbody');
            // Normalize header names so we can read associative row data reliably
            const headerKeys = result.preview.headers.map((header) => ({
                original: header.name,
                normalized: header.name
                    .toString()
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, '_')
                    .replace(/[^a-z0-9_]/g, '')
            }));

            result.preview.data.forEach(rowData => {
                const tr = document.createElement('tr');

                headerKeys.forEach((headerKey, index) => {
                    const td = document.createElement('td');
                    td.className = 'small';

                    let value = '';
                    if (Array.isArray(rowData)) {
                        value = rowData[index] ?? '';
                    } else if (rowData && typeof rowData === 'object') {
                        value = rowData[headerKey.original] ??
                                rowData[headerKey.normalized] ??
                                '';
                    }

                    td.textContent = value ?? '';
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            container.appendChild(table);
            previewSection.classList.remove('d-none');
        }
        
        if (result.is_new_format) {
            if (confirm(result.message + ' Proceed?')) {
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
                this.innerHTML = '<i class="fas fa-search me-2"></i>Check Format';
        console.error('Error:', error);
        alert('An error occurred while validating the format. Please try again.');
    }
});

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('file-upload');
    
    // Validate the file type before submitting
    if (!isValidFileType(fileInput.files[0].name)) {
        alert('File must be in XLSX, XLS, or CSV format.');
        return;
    }
    
    const uploadBtn = document.getElementById('uploadBtn');
    const formData = new FormData(this);
    
    document.getElementById('loading').classList.remove('d-none');
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
    
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
            throw new Error(result.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
        document.getElementById('loading').classList.add('d-none');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload & Process';
    }
});
</script>
@endpush
