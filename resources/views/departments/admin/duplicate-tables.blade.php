@extends('layouts.app')

@section('title', 'Duplicate Table Detection')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h2 class="mb-0">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>Duplicate Table Detection
        </h2>
        <p class="text-muted mb-0 mt-2">Identify tables that share an identical structure but use different names.</p>
    </div>

    <div class="card-body">
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>What is a duplicate table?</strong><br>
            The system flags tables that contain identical column structures yet use different names.
            This situation can create data redundancy and slow overall performance.
        </div>

        @if(empty($duplicates))
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <p class="text-success mt-3 mb-0 fw-bold">No duplicate tables were detected.</p>
            <p class="text-muted">Every table currently uses a unique structure.</p>
        </div>
        @else
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ count($duplicates) }} potential duplicate tables were detected.</strong>
        </div>

        @foreach($duplicates as $index => $dup)
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0">
                    <i class="fas fa-clone me-2"></i>Duplicate #{{ $index + 1 }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <p class="mb-1"><strong>Original table:</strong></p>
                        <code class="bg-light px-3 py-2 rounded d-inline-block">{{ $dup['original_table'] }}</code>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-equals text-muted" style="font-size: 2rem; margin-top: 10px;"></i>
                    </div>
                    <div class="col-md-5">
                        <p class="mb-1"><strong>Duplicate table:</strong></p>
                        <code class="bg-light px-3 py-2 rounded d-inline-block">{{ $dup['duplicate_table'] }}</code>
                    </div>
                </div>

                <hr>

                <p class="mb-2"><strong>Matching column structure:</strong></p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($dup['columns'] as $col)
                        <span class="badge badge-soft-neutral">{{ $col }}</span>
                    @endforeach
                </div>

                <div class="alert alert-light mt-3 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Recommendation:</strong> Consider consolidating into a single table or renaming columns if they serve different functions.
                </div>
            </div>
        </div>
        @endforeach

        <div class="alert alert-primary mt-4" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-tools me-2"></i>Suggested remediation steps
            </h6>
            <ol class="mb-0">
                <li>Review whether the tables truly require separation.</li>
                <li>If not, map future imports to a single shared table.</li>
                <li>If separation is required, add unique identifier columns.</li>
                <li>Perform data migration where necessary.</li>
            </ol>
        </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('admin.master-data.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Master Data
            </a>
        </div>
    </div>
</div>
@endsection
