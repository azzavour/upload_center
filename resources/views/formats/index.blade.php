@extends('layouts.app')

@section('title', 'Excel Formats')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-file-excel text-green-600 mr-2"></i>Format Excel Terdaftar
            </h2>
            <p class="text-gray-600 mt-1">Kelola format Excel yang dapat digunakan untuk upload</p>
        </div>
        <a href="{{ route('formats.create') }}" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Tambah Format Baru
        </a>
    </div>

    <div class="p-6">
        @if($formats->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada format terdaftar</p>
            <a href="{{ route('formats.create') }}" 
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Tambah Format Pertama
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($formats as $format)
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <i class="fas fa-table text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $format->format_name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $format->format_code }}</code>
                                </p>
                            </div>
                        </div>
                        
                        @if($format->description)
                        <p class="mt-3 text-sm text-gray-600">
                            {{ $format->description }}
                        </p>
                        @endif

                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-columns mr-1"></i>Kolom yang Diharapkan:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($format->expected_columns as $column)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $column }}
                                </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <i class="fas fa-database mr-2"></i>
                            Target Table: <code class="ml-1 bg-gray-100 px-2 py-1 rounded">{{ $format->target_table }}</code>
                        </div>
                    </div>
                    
                    <div>
                        @if($format->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-times-circle mr-1"></i>Nonaktif
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection