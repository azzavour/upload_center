@extends('layouts.app')

@section('title', 'Detail Upload')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-file-alt text-blue-600 mr-2"></i>Detail Upload
                </h2>
                <p class="text-gray-600 mt-1">{{ $history->original_filename }}</p>
            </div>
            <a href="{{ route('history.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="p-6">
        <!-- Status Banner -->
        <div class="mb-6">
            @if($history->status === 'completed')
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-green-800">Upload Berhasil!</h3>
                        <p class="text-sm text-green-700 mt-1">
                            File telah berhasil diproses dan data telah dimasukkan ke database.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($history->status === 'failed')
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-red-800">Upload Gagal!</h3>
                        <p class="text-sm text-red-700 mt-1">
                            Terjadi kesalahan saat memproses file. Silakan cek detail error di bawah.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($history->status === 'processing')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-spinner fa-spin text-yellow-400 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">Sedang Diproses...</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            File sedang dalam proses. Mohon tunggu beberapa saat.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Upload Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Informasi File
                </h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-gray-500">Nama File Asli</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $history->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Nama File Tersimpan</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $history->stored_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Waktu Upload</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $history->uploaded_at->format('d M Y H:i:s') }}
                            <span class="text-xs text-gray-500">({{ $history->uploaded_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-700 mb-3">
                    <i class="fas fa-table mr-2"></i>Format & Mapping
                </h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-blue-600">Format Excel</dt>
                        <dd class="text-sm font-medium text-blue-900">{{ $history->excelFormat->format_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-blue-600">Format Code</dt>
                        <dd class="text-sm font-medium text-blue-900">
                            <code class="bg-blue-100 px-2 py-1 rounded">{{ $history->excelFormat->format_code }}</code>
                        </dd>
                    </div>
                    @if($history->mappingConfiguration)
                    <div>
                        <dt class="text-xs text-blue-600">Mapping Index</dt>
                        <dd class="text-sm font-medium text-blue-900">
                            <code class="bg-purple-100 px-2 py-1 rounded text-purple-700">
                                {{ $history->mappingConfiguration->mapping_index }}
                            </code>
                        </dd>
                    </div>
                    @else
                    <div>
                        <dt class="text-xs text-blue-600">Mapping</dt>
                        <dd class="text-sm font-medium text-blue-900">Format Standar (Tanpa Mapping)</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-blue-600">Target Table</dt>
                        <dd class="text-sm font-medium text-blue-900">
                            <code class="bg-blue-100 px-2 py-1 rounded">{{ $history->excelFormat->target_table }}</code>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Statistics -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2"></i>Statistik Import
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gray-100 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $history->total_rows }}</div>
                    <div class="text-sm text-gray-600 mt-1">Total Baris</div>
                </div>
                <div class="bg-green-100 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-green-700">{{ $history->success_rows }}</div>
                    <div class="text-sm text-green-600 mt-1">Berhasil</div>
                </div>
                <div class="bg-red-100 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-red-700">{{ $history->failed_rows }}</div>
                    <div class="text-sm text-red-600 mt-1">Gagal</div>
                </div>
                <div class="bg-blue-100 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-blue-700">
                        {{ $history->total_rows > 0 ? round(($history->success_rows / $history->total_rows) * 100, 1) : 0 }}%
                    </div>
                    <div class="text-sm text-blue-600 mt-1">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Error Details -->
        @if($history->failed_rows > 0 && $history->error_details)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-red-900 mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>Detail Error ({{ count($history->error_details) }} error)
            </h3>
            <div class="bg-red-50 rounded-lg overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-red-200">
                        <thead class="bg-red-100 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                    Baris
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                    Error Message
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                    Data
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-red-100">
                            @foreach($history->error_details as $error)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-900">
                                    Baris {{ $error['row'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-800">
                                    {{ $error['error'] ?? 'Unknown error' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    @if(isset($error['data']) && is_array($error['data']))
                                        <details class="cursor-pointer">
                                            <summary class="text-blue-600 hover:text-blue-800">Lihat Data</summary>
                                            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ json_encode($error['data'], JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Column Mapping Used -->
        @if($history->mappingConfiguration)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-arrows-alt-h mr-2"></i>Column Mapping yang Digunakan
            </h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($history->mappingConfiguration->column_mapping as $excelCol => $dbCol)
                    <div class="flex items-center text-sm bg-white p-2 rounded border border-gray-200">
                        <code class="bg-yellow-50 px-2 py-1 rounded border border-yellow-200 text-yellow-800">{{ $excelCol }}</code>
                        <i class="fas fa-long-arrow-alt-right mx-2 text-gray-400"></i>
                        <code class="bg-blue-50 px-2 py-1 rounded border border-blue-200 text-blue-800">{{ $dbCol }}</code>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <a href="{{ route('history.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke History
            </a>
            <a href="{{ route('upload.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>
                Upload File Baru
            </a>
        </div>
    </div>
</div>
@endsection