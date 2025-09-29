@extends('layouts.app')

@section('title', 'Buat Data Mapping')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-project-diagram text-blue-600 mr-2"></i>Buat Data Mapping
        </h2>
        <p class="text-gray-600 mt-1">Mapping kolom Excel ke kolom database untuk format: <strong>{{ $format->format_name }}</strong></p>
    </div>

    <div class="p-6">
        <form action="{{ route('mapping.store') }}" method="POST">
            @csrf
            <input type="hidden" name="excel_format_id" value="{{ $format->id }}">

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Petakan kolom dari file Excel Anda ke kolom database yang sesuai. 
                            <strong>1 Mapping = 1 Tabel Database ({{ $format->target_table }})</strong>
                        </p>
                    </div>
                </div>
            </div>

            @if(!empty($excelColumns))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-semibold mb-2">
                            Kolom Excel yang Terdeteksi:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($excelColumns as $col)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $col }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kolom Excel
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-arrow-right"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kolom Database
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transformasi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $dbColumns = [
                                'track_id' => 'Track ID (Required)',
                                'track_name' => 'Track Name (Required)',
                                'artist_id' => 'Artist ID (Required)',
                                'artist_name' => 'Artist Name (Required)',
                                'album_name' => 'Album Name',
                                'genre' => 'Genre',
                                'release_date' => 'Release Date',
                                'track_price' => 'Track Price',
                                'collection_price' => 'Collection Price',
                                'country' => 'Country'
                            ];
                        @endphp

                        @foreach($dbColumns as $dbCol => $label)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" 
                                    class="excel-column-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Contoh: Track ID atau id_track"
                                    id="excel_{{ $dbCol }}"
                                    value="{{ $excelColumns[array_search($dbCol, array_map('strtolower', array_map('trim', str_replace(' ', '_', $excelColumns)))) ?? ''] ?? '' }}">
                            </td>
                            <td class="px-6 py-4 text-center">
                                <i class="fas fa-long-arrow-alt-right text-gray-400"></i>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <code class="bg-gray-100 px-3 py-1 rounded text-sm font-mono">{{ $dbCol }}</code>
                                    <span class="ml-2 text-sm text-gray-600">{{ $label }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select name="transformation_rules[{{ $dbCol }}][type]" 
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tidak Ada</option>
                                    <option value="trim">Trim (Hapus Spasi)</option>
                                    <option value="uppercase">UPPERCASE</option>
                                    <option value="lowercase">lowercase</option>
                                    @if($dbCol === 'release_date')
                                    <option value="date_format">Format Tanggal</option>
                                    @endif
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-semibold text-yellow-900 mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Contoh Mapping
                </h4>
                <div class="text-sm text-yellow-800">
                    <p><strong>Jika Excel Anda memiliki kolom:</strong> "id_track", "nama_lagu", "nama_artis"</p>
                    <p class="mt-1"><strong>Maka mapping-nya:</strong></p>
                    <ul class="list-disc list-inside ml-4 mt-1">
                        <li>"id_track" → <code>track_id</code></li>
                        <li>"nama_lagu" → <code>track_name</code></li>
                        <li>"nama_artis" → <code>artist_name</code></li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('upload.index') }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <button type="submit" 
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Mapping
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('form').addEventListener('submit', function(e) {
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
        e.preventDefault();
        alert('Minimal isi satu mapping kolom!');
        return;
    }
    
    // Tambahkan hidden input untuk column mapping
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'column_mapping';
    hiddenInput.value = JSON.stringify(columnMapping);
    this.appendChild(hiddenInput);
});
</script>
@endpush