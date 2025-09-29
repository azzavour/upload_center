@extends('layouts.app')

@section('title', 'Upload Data')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-upload text-blue-600 mr-2"></i>Upload Data Excel
        </h2>
        <p class="text-gray-600 mt-1">Upload file Excel untuk data musik/tracks</p>
    </div>

    <div class="p-6">
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-list-alt mr-1"></i>Pilih Format Excel
                </label>
                <select name="format_id" id="format_id" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Format --</option>
                    @foreach($formats as $format)
                        <option value="{{ $format->id }}">
                            {{ $format->format_name }} ({{ $format->format_code }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle"></i> 
                    Format yang diharapkan: Track ID, Track Name, Artist ID, Artist Name, Album Name, Genre, Release Date, Track Price, Collection Price, Country
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-excel mr-1"></i>Pilih File Excel
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                <span>Upload file</span>
                                <input id="file-upload" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                            </label>
                            <p class="pl-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">XLSX, XLS, CSV hingga 10MB</p>
                        <p id="file-name" class="text-sm text-green-600 font-medium mt-2"></p>
                    </div>
                </div>
            </div>

            <div id="mapping-section" class="mb-6 hidden">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Format Baru Terdeteksi!</strong> 
                                Kolom Excel tidak sesuai dengan format yang terdaftar. Silakan lakukan mapping kolom.
                            </p>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="mapping_id" id="mapping_id">
            </div>

            <div class="flex items-center justify-between">
                <button type="button" id="checkBtn" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class="fas fa-search mr-2"></i>
                    Cek Format
                </button>
                
                <button type="submit" id="uploadBtn" disabled
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-upload mr-2"></i>
                    Upload & Process
                </button>
            </div>
        </form>

        <div id="loading" class="hidden mt-6">
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Memproses file...</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-blue-900 mb-2">
        <i class="fas fa-lightbulb mr-2"></i>Panduan Upload
    </h3>
    <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
        <li>Pastikan file Excel memiliki header di baris pertama</li>
        <li>Kolom yang wajib ada: Track ID, Track Name, Artist Name</li>
        <li>Format tanggal: YYYY-MM-DD atau DD/MM/YYYY</li>
        <li>Harga gunakan format angka tanpa simbol mata uang</li>
        <li>Jika kolom berbeda, sistem akan meminta Anda melakukan mapping</li>
    </ul>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('file-upload').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileNameDisplay = document.getElementById('file-name');
    if (fileName) {
        fileNameDisplay.textContent = '✓ ' + fileName;
    }
});

document.getElementById('checkBtn').addEventListener('click', async function() {
    const formData = new FormData();
    const fileInput = document.getElementById('file-upload');
    const formatId = document.getElementById('format_id').value;
    
    if (!fileInput.files[0] || !formatId) {
        alert('Pilih format dan file terlebih dahulu!');
        return;
    }
    
    // Disable button saat loading
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memeriksa...';
    
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
        
        // Parse JSON response
        const result = await response.json();
        
        // Enable button kembali
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-search mr-2"></i>Cek Format';
        
        if (result.error) {
            alert('Error: ' + result.message);
            return;
        }
        
        if (result.is_new_format) {
            document.getElementById('mapping-section').classList.remove('hidden');
            
            if (confirm('Format baru terdeteksi! Anda akan diarahkan ke halaman mapping. Lanjutkan?')) {
                window.location.href = result.redirect;
            }
        } else {
            alert(result.message);
            document.getElementById('uploadBtn').disabled = false;
        }
    } catch (error) {
        // Enable button kembali
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-search mr-2"></i>Cek Format';
        
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memeriksa format. Silakan coba lagi.');
    }
});

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const uploadBtn = document.getElementById('uploadBtn');
    const formData = new FormData(this);
    
    document.getElementById('loading').classList.remove('hidden');
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupload...';
    
    try {
        const response = await fetch('{{ route("upload.process") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        });
        
        if (response.ok) {
            // Jika sukses dan ada redirect
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
        document.getElementById('loading').classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload & Process';
    }
});
</script>
@endpush