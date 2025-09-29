@extends('layouts.app')

@section('title', 'Tambah Format Baru')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Tambah Format Excel Baru
        </h2>
        <p class="text-gray-600 mt-1">Daftarkan format Excel baru untuk sistem upload</p>
    </div>

    <div class="p-6">
        <form action="{{ route('formats.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Format <span class="text-red-500">*</span>
                </label>
                <input type="text" name="format_name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: Music Tracks Format Standard">
                @error('format_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi
                </label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Deskripsi singkat tentang format ini"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kolom yang Diharapkan <span class="text-red-500">*</span>
                </label>
                <div id="columns-container" class="space-y-2 mb-3">
                    <!-- Kolom default untuk tracks -->
                    <div class="flex gap-2 column-row">
                        <input type="text" name="expected_columns[]" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                            value="Track ID">
                        <button type="button" onclick="removeColumn(this)" 
                            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex gap-2 column-row">
                        <input type="text" name="expected_columns[]" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                            value="Track Name">
                        <button type="button" onclick="removeColumn(this)" 
                            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex gap-2 column-row">
                        <input type="text" name="expected_columns[]" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                            value="Artist ID">
                        <button type="button" onclick="removeColumn(this)" 
                            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex gap-2 column-row">
                        <input type="text" name="expected_columns[]" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                            value="Artist Name">
                        <button type="button" onclick="removeColumn(this)" 
                            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addColumn()" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Kolom
                </button>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Table <span class="text-red-500">*</span>
                </label>
                <input type="text" name="target_table" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    value="tracks"
                    placeholder="Nama tabel di database (contoh: tracks)">
                @error('target_table')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Catatan:</strong> Pastikan nama kolom sesuai dengan header di file Excel Anda. 
                            Target table harus sudah ada di database PostgreSQL.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('formats.index') }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <button type="submit" 
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Format
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
    div.className = 'flex gap-2 column-row';
    div.innerHTML = `
        <input type="text" name="expected_columns[]" required
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
            placeholder="Nama kolom (contoh: Album Name)">
        <button type="button" onclick="removeColumn(this)" 
            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
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