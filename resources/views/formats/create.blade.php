@extends('layouts.app')

@section('title', 'Add New Format')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-plus-circle text-primary me-2"></i>Add a New Excel Format
        </h2>
        <p class="text-muted mb-0 mt-2">Register a new Excel format for the upload workflow.</p>
    </div>

    <div class="card-body">
        <form action="{{ route('formats.store') }}" method="POST">
            @csrf

            <!-- Format Name -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Format Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="format_name" required class="form-control"
                    placeholder="Example: Product Data Format 2025">
                @error('format_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" rows="3" class="form-control"
                    placeholder="Brief description of this format"></textarea>
            </div>

            <!-- Expected Columns -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Expected Columns <span class="text-danger">*</span>
                </label>
                <div id="columns-container" class="mb-3">
                    <!-- Initial column (at least one required) -->
                    <div class="input-group mb-2 column-row">
                        <input type="text" name="expected_columns[]" required class="form-control" 
                               placeholder="Example: Product Code, Name, Date, etc.">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addColumn()" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-plus me-2"></i>Add Column
                </button>
                <div class="form-text mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Add columns exactly as they appear in your Excel headers (minimum one column).
                </div>
            </div>

            <!-- Target Table -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-table me-1"></i>New Table Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="target_table" id="target_table" required class="form-control" 
                    placeholder="Enter table name (example: products, employees, sales)"
                    style="text-transform: lowercase;">
                @error('target_table')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Table names are forced to lowercase and may only contain letters, numbers, and underscores (_).
                </div>
                <div class="alert alert-success mt-2 mb-0">
                    <i class="fas fa-magic me-1"></i>
                    <strong>The table will be created automatically.</strong><br>
                    <small>
                        The system creates a new database table using the convention: 
                        <code class="bg-white px-2 py-1 rounded">dept_[your_department_code]_[table_name]</code>
                    </small>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb me-2"></i>Completion Guidance
                </h6>
                <ul class="mb-0 small">
                    <li><strong>Format name:</strong> Provide a descriptive title, for example “Sales Report Format Q1 2025”.</li>
                    <li><strong>Columns:</strong> List them exactly as they appear in Excel, e.g., Date, Customer Name, Total Purchase.</li>
                    <li><strong>Target table:</strong> Define the destination table such as sales, employees, or products.</li>
                    <li><strong>Automation:</strong> The system prefixes each table with your department code.</li>
                </ul>
            </div>

            <!-- Examples Box -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title text-primary mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>Format Examples
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Product Data Format</strong>
                                <div class="small text-muted">
                                    Columns: Product Code, Product Name, Category, Price, Stock<br>
                                    Target: <code>produk</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Employee Data Format</strong>
                                <div class="small text-muted">
                                    Columns: Employee ID, Name, Position, Department, Salary<br>
                                    Target: <code>karyawan</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Sales Report Format</strong>
                                <div class="small text-muted">
                                    Columns: Date, Invoice No, Customer, Total, Status<br>
                                    Target: <code>penjualan</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border">
                                <strong class="d-block mb-2">Inventory Format</strong>
                                <div class="small text-muted">
                                    Columns: SKU, Item Name, Location, Quantity, Unit<br>
                                    Target: <code>inventory</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('formats.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Format
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
    div.className = 'input-group mb-2 column-row';
    div.innerHTML = `
        <input type="text" name="expected_columns[]" required class="form-control"
            placeholder="Column name (example: Date, Amount, Notes)">
        <button type="button" onclick="removeColumn(this)" class="btn btn-danger">
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
        alert('At least one column is required.');
    }
}

// Auto-sanitize table name
document.getElementById('target_table').addEventListener('input', function(e) {
    let value = e.target.value;
    // Convert to lowercase
    value = value.toLowerCase();
    // Replace spaces with underscore
    value = value.replace(/\s+/g, '_');
    // Remove invalid characters (keep only a-z, 0-9, _)
    value = value.replace(/[^a-z0-9_]/g, '');
    // Update value
    e.target.value = value;
});
</script>
@endpush
