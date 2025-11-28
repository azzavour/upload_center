@extends('layouts.app')

@section('title', 'Mapping Details')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-project-diagram text-primary me-2"></i>Mapping Details
            </h2>
            <p class="text-muted mb-0 mt-2">{{ $mapping->mapping_index }}</p>
        </div>
        <a href="{{ route('mapping.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card-body">
        <!-- Info Format -->
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>Format Information
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Format Name:</strong><br>
                    {{ $mapping->excelFormat->format_name }}
                </div>
                <div class="col-md-6">
                    <strong>Format Code:</strong><br>
                    {{ $mapping->excelFormat->format_code }}
                </div>
                <div class="col-md-6">
                    <strong>Target Table:</strong><br>
                    <code class="bg-white px-2 py-1 rounded">{{ $mapping->excelFormat->target_table }}</code>
                </div>
                <div class="col-md-6">
                    <strong>Created:</strong><br>
                    {{ $mapping->created_at->format('d M Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Column Mapping Table -->
        <div class="mb-4">
            <h5 class="mb-3">
                <i class="fas fa-table me-2"></i>Column Mapping Configuration
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Excel Column</th>
                            <th class="text-center" width="50"><i class="fas fa-arrow-right"></i></th>
                            <th>Database Column</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mapping->column_mapping as $index => $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <code class="bg-warning bg-opacity-25 border border-warning px-3 py-2 rounded">
                                    {{ $index }}
                                </code>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-long-arrow-alt-right text-primary fs-5"></i>
                            </td>
                            <td>
                                <code class="bg-info bg-opacity-25 border border-info px-3 py-2 rounded text-primary">
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
        <div class="mb-4">
            <h5 class="mb-3">
                <i class="fas fa-magic me-2"></i>Transformation Rules
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Field</th>
                            <th>Transformation Type</th>
                            <th>Additional Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mapping->transformation_rules as $field => $rule)
                        <tr>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $field }}</code>
                            </td>
                            <td>
                                <span class="badge badge-soft-neutral">
                                    {{ $rule['type'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-muted small">
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
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h6 class="card-title text-success">
                    <i class="fas fa-code me-1"></i>JSON Configuration
                </h6>
                <pre class="text-success mb-0" style="font-size: 0.85rem;"><code>{{ json_encode([
                    'mapping_index' => $mapping->mapping_index,
                    'column_mapping' => $mapping->column_mapping,
                    'transformation_rules' => $mapping->transformation_rules
                ], JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection
