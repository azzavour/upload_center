@extends('layouts.app')

@section('title', 'Detail Mapping')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-project-diagram text-blue-600 mr-2"></i>Detail Data Mapping
                </h2>
                <p class="text-gray-600 mt-1">{{ $mapping->mapping_index }}</p>
            </div>
            <a href="{{ route('mapping.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="p-6">
        <!-- Info Format -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Informasi Format
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-blue-700"><strong>Format Name:</strong></p>
                    <p class="text-blue-800">{{ $mapping->excelFormat->format_name }}</p>
                </div>
                <div>
                    <p class="text-blue-700"><strong>Format Code:</strong></p>
                    <p class="text-blue-800">{{ $mapping->excelFormat->format_code }}</p>
                </div>
                <div>
                    <p class="text-blue-700"><strong>Target Table:</strong></p>
                    <p class="text-blue-800">
                        <code class="bg-blue-100 px-2 py-1 rounded">{{ $mapping->excelFormat->target_table }}</code>
                    </p>
                </div>
                <div>
                    <p class="text-blue-700"><strong>Dibuat:</strong></p>
                    <p class="text-blue-800">{{ $mapping->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Column Mapping Table -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-table mr-2"></i>Column Mapping Configuration
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Excel Column
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-arrow-right"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Database Column
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mapping->column_mapping as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="bg-yellow-50 px-3 py-1 rounded border border-yellow-200 text-yellow-800 font-mono text-sm">
                                    {{ $index }}
                                </code>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <i class="fas fa-long-arrow-alt-right text-blue-500 text-xl"></i>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="bg-blue-50 px-3 py-1 rounded border border-blue-200 text-blue-800 font-mono text-sm">
                                    {{ $item }}
                                </code>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transformation Rules -->
        @if($mapping->transformation_rules && count($mapping->transformation_rules) > 0)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-magic mr-2"></i>Transformation Rules
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-purple-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">
                                Field
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">
                                Transformation Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">
                                Additional Info
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mapping->transformation_rules as $field => $rule)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="bg-gray-100 px-3 py-1 rounded font-mono text-sm">{{ $field }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $rule['type'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if(isset($rule['format']))
                                    Format: <code>{{ $rule['format'] }}</code>
                                @elseif(isset($rule['search']) && isset($rule['replace']))
                                    Replace "{{ $rule['search'] }}" with "{{ $rule['replace'] }}"
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
        @endif

        <!-- JSON Preview -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-code mr-1"></i>JSON Configuration
            </h4>
            <pre class="bg-gray-900 text-green-400 p-4 rounded text-xs overflow-x-auto"><code>{{ json_encode([
                'mapping_index' => $mapping->mapping_index,
                'column_mapping' => $mapping->column_mapping,
                'transformation_rules' => $mapping->transformation_rules
            ], JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
</div>
@endsection