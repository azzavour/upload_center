@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>{{ __('Create Account') }}
                    </h3>
                    <p class="mb-0 small mt-2">Join Upload Center today</p>
                </div>

                <div class="card-body p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-user me-1"></i>{{ __('Full Name') }}
                            </label>
                            <input id="name" type="text" 
                                class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autocomplete="name" 
                                autofocus
                                placeholder="Enter your full name">

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-1"></i>{{ __('Email Address') }}
                            </label>
                            <input id="email" type="email" 
                                class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autocomplete="email"
                                placeholder="your.email@example.com">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Department Selection -->
                        <div class="mb-4">
                            <label for="department_id" class="form-label fw-bold">
                                <i class="fas fa-building me-1"></i>Department <span class="text-danger">*</span>
                            </label>
                            <select id="department_id" 
                                class="form-select form-select-lg @error('department_id') is-invalid @enderror" 
                                name="department_id" 
                                required>
                                <option value="">-- Select Your Department --</option>
                                @foreach(\App\Models\Department::active()->orderBy('name')->get() as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Select the department you belong to
                            </small>

                            @error('department_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-lock me-1"></i>{{ __('Password') }}
                            </label>
                            <input id="password" type="password" 
                                class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                name="password" 
                                required 
                                autocomplete="new-password"
                                placeholder="Min. 8 characters">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password-confirm" class="form-label fw-bold">
                                <i class="fas fa-lock me-1"></i>{{ __('Confirm Password') }}
                            </label>
                            <input id="password-confirm" type="password" 
                                class="form-control form-control-lg" 
                                name="password_confirmation" 
                                required 
                                autocomplete="new-password"
                                placeholder="Re-enter your password">
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>{{ __('Register') }}
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">
                                    Login here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-4" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> After registration, you can upload files according to your department's format.
            </div>
        </div>
    </div>
</div>
@endsection