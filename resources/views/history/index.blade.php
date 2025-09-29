@extends('layouts.app')

@section('title', 'Upload History')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-history text-blue-600 mr-2"></i>Upload History
                </h2>
                <p class="text-gray-600 mt-1">Daftar riwayat upload file Excel</p>
            </div>
            <a href="{{ route('upload.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>
                Upload Baru
            </a>
        </div>
    </div>

    <div class="p-6">
        @if(session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($histories->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada riwayat upload</p>
            <p class="text-gray-400 text-sm mt-2">Upload file pertama Anda untuk memulai</p>
            <a href="{{ route('upload.index') }}" 
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>
                Upload File
            </a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            File
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Format
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mapping
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistik
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu Upload
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($histories as $history)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="fas fa-file-excel text-green-600 text-xl mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $history->original_filename }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $history->stored_filename }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $history->excelFormat->format_name }}</div>
                            <div class="text-xs text-gray-500">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $history->excelFormat->format_code }}</code>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->mappingConfiguration)
                            <code class="text-xs bg-purple-100 px-2 py-1 rounded text-purple-700">
                                {{ $history->mappingConfiguration->mapping_index }}
                            </code>
                            @else
                            <span class="text-xs text-gray-400">Standar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->status === 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Selesai
                            </span>
                            @elseif($history->status === 'failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                Gagal
                            </span>
                            @elseif($history->status === 'processing')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Proses
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-clock mr-1"></i>
                                {{ ucfirst($history->status) }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    <span class="text-gray-500">Total:</span>
                                    <span class="ml-1 font-medium text-gray-900">{{ $history->total_rows }}</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <div class="flex items-center">
                                    <span class="text-green-600"><i class="fas fa-check text-xs"></i></span>
                                    <span class="ml-1 font-medium text-green-700">{{ $history->success_rows }}</span>
                                </div>
                                @if($history->failed_rows > 0)
                                <span class="text-gray-300">|</span>
                                <div class="flex items-center">
                                    <span class="text-red-600"><i class="fas fa-times text-xs"></i></span>
                                    <span class="ml-1 font-medium text-red-700">{{ $history->failed_rows }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $history->uploaded_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $history->uploaded_at->format('H:i:s') }}</div>
                            <div class="text-xs text-gray-400">{{ $history->uploaded_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('history.show', $history->id) }}" 
                                class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-eye mr-1"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination (jika diperlukan) -->
        <div class="mt-6">
            {{-- {{ $histories->links() }} --}}
        </div>
        @endif
    </div>
</div>
@endsection