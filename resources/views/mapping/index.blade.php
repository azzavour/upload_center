@extends('layouts.app')

@section('title', 'Data Mapping')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-project-diagram text-blue-600 mr-2"></i>Data Mapping Terdaftar
        </h2>
        <p class="text-gray-600 mt-1">Lihat semua konfigurasi mapping yang telah dibuat</p>
    </div>

    <div class="p-6">
        @if($mappings->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-project-diagram text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada mapping terdaftar</p>
            <p class="text-gray-400 text-sm mt-2">Mapping akan otomatis dibuat saat Anda upload file dengan format baru</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($mappings as $mapping)
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-fingerprint mr-2"></i>
                                {{ $mapping->mapping_index }}
                            </span>
                            <span class="text-sm text-gray-500">
                                dibuat {{ $mapping->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-table mr-1"></i>Format: 
                                <span class="text-blue-600">{{ $mapping->excelFormat->format_name }}</span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-database mr-1"></i>Target Table: 
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $mapping->excelFormat->target_table }}</code>
                            </p>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-arrows-alt-h mr-1"></i>Column Mapping:
                            </p>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($mapping->column_mapping as $excelCol => $dbCol)
                                    <div class="flex items-center text-sm">
                                        <code class="bg-white px-2 py-1 rounded border border-gray-200">{{ $excelCol }}</code>
                                        <i class="fas fa-long-arrow-alt-right mx-2 text-gray-400"></i>
                                        <code class="bg-blue-50 px-2 py-1 rounded border border-blue-200 text-blue-700">{{ $dbCol }}</code>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @if($mapping->transformation_rules && count($mapping->transformation_rules) > 0)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-magic mr-1"></i>Transformation Rules:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($mapping->transformation_rules as $field => $rule)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $field }}: {{ $rule['type'] ?? 'N/A' }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('mapping.show', $mapping->id) }}" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-2"></i>
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection