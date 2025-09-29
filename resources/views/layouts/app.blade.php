<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Upload Center')</title>
    
    {{-- Memuat pustaka CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- CSS tambahan untuk memposisikan form login/register di tengah --}}
    <link rel="stylesheet" href="{{ asset('css/custom-auth.css') }}">
     @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- TAMBAHKAN BARIS INI --}}
    <link rel="stylesheet" href="{{ asset('css/custom-auth.css') }}">
</head>
<body class="bg-gray-100">
    <div id="app">
        {{-- START: Navbar (Sudah digabung menjadi satu) --}}
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Logo & Nama Aplikasi --}}
                    <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-cloud-upload-alt text-blue-600 mr-2"></i>
                        Upload Center
                    </a>
                    
                    {{-- Menu Navigasi --}}
                    <div class="hidden sm:flex items-center space-x-6">
                        @guest
                            {{-- Tampilan untuk tamu (belum login) --}}
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                    <i class="fas fa-user-plus mr-2"></i>Register
                                </a>
                            @endif
                        @else
                            {{-- Tampilan untuk pengguna yang sudah login --}}
                            <a href="{{ route('upload.index') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                <i class="fas fa-upload mr-2"></i>Upload
                            </a>
                            <a href="{{ route('mapping.index') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                <i class="fas fa-project-diagram mr-2"></i>Mapping
                            </a>
                            <a href="{{ route('history.index') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                <i class="fas fa-history mr-2"></i>Riwayat
                            </a>
                            <a href="{{ route('formats.index') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                                <i class="fas fa-file-alt mr-2"></i>Format
                            </a>

                            {{-- Informasi Pengguna & Tombol Logout --}}
                            <div class="pl-4 border-l border-gray-200 flex items-center">
                                <span class="text-gray-800 font-medium mr-4">{{ Auth::user()->name }}</span>
                                <a href="{{ route('logout') }}"
                                   class="text-red-600 hover:text-red-800"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                   title="Logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </nav>
        {{-- END: Navbar --}}

        {{-- START: Konten Utama --}}
        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                {{-- Pesan Sukses atau Error --}}
                @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
                @endif

                {{-- Konten dari setiap halaman akan ditampilkan di sini --}}
                @yield('content')
                
            </div>
        </main>
        {{-- END: Konten Utama --}}
    </div>

    {{-- START: Footer --}}
    <footer class="bg-white shadow-lg mt-10">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; 2025 Upload Center. Powered by Laravel & PostgreSQL
            </p>
        </div>
    </footer>
    {{-- END: Footer --}}

    @stack('scripts')
</body>
</html>

