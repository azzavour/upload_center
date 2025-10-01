<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Upload Center')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite Assets (Bootstrap + Custom CSS) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <i class="fas fa-cloud-upload-alt text-primary"></i>
                    {{ config('app.name', 'Upload Center') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side -->
                    <ul class="navbar-nav me-auto">
                        @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}" href="{{ route('upload.index') }}">
                                <i class="fas fa-upload"></i> Upload
                            </a>
                        </li>
                       <li class="nav-item">
                           <a class="nav-link {{ request()->routeIs('department-uploads.*') ? 'active' : '' }}" href="{{ route('department-uploads.index') }}">
                                 <i class="fas fa-building"></i> Department Uploads
                         </a>
                    </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mapping.*') ? 'active' : '' }}" href="{{ route('mapping.index') }}">
                                <i class="fas fa-project-diagram"></i> Mapping
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}" href="{{ route('history.index') }}">
                                <i class="fas fa-history"></i> Riwayat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('formats.*') ? 'active' : '' }}" href="{{ route('formats.index') }}">
                                <i class="fas fa-file-excel"></i> Format
                            </a>
                        </li>

                        @if(auth()->user()->isAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}" 
                                href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-shield-alt text-danger"></i> Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.departments.index') }}">
                                        <i class="fas fa-building me-2"></i>Departments
                                    </a>
                                </li>
                                  <a class="dropdown-item" href="{{ route('admin.user-activity.index') }}">
        <i class="fas fa-chart-line me-2"></i>User Activity
    </a>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.master-data.index') }}">
                                        <i class="fas fa-database me-2"></i>Master Data
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.all-uploads') }}">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>All Uploads
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.master-data.duplicates') }}">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Duplicate Tables
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
</li>
                        @endif
                        @endauth
                    </ul>

                    <!-- Right Side -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt"></i> {{ __('Login') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus"></i> {{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                                    @if(Auth::user()->department)
                                        <span class="badge bg-primary ms-1">{{ Auth::user()->department->code }}</span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    @if(Auth::user()->isAdmin())
                                    <div class="dropdown-header">
                                        <i class="fas fa-crown text-warning me-1"></i>
                                        <strong>Admin Master</strong>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    
                                    @if(Auth::user()->department)
                                    <div class="dropdown-item-text small">
                                        <i class="fas fa-building me-1"></i>
                                        {{ Auth::user()->department->name }}
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    @endif

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4 {{ request()->routeIs('login') || request()->routeIs('register') ? 'auth-center' : '' }}">
            <div class="container">
                <!-- Success Alert -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Error Alert -->
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top mt-auto py-3">
            <div class="container text-center text-muted">
                <small>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</small>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>