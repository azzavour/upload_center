<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Center - Multi-Department Data Management</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            min-height: 70vh;
            display: flex;
            align-items: center;
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-section {
            background: #f8f9fa;
            padding: 60px 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                <strong>Upload Center</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="btn btn-primary" href="{{ route('upload.index') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Multi-Department<br>Data Management System
                    </h1>
                    <p class="lead mb-4">
                        Upload, manage, and organize your Excel data across multiple departments with ease. 
                        Built for enterprises that need data isolation and security.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Get Started
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-file-excel" style="font-size: 15rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-building stat-number"></i>
                        <h3 class="stat-number">∞</h3>
                        <p class="text-muted">Departments</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-shield-alt stat-number"></i>
                        <h3 class="stat-number">100%</h3>
                        <p class="text-muted">Data Isolated</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-file-excel stat-number"></i>
                        <h3 class="stat-number">40MB</h3>
                        <p class="text-muted">Max File Size</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-bolt stat-number"></i>
                        <h3 class="stat-number">Fast</h3>
                        <p class="text-muted">Processing</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
                <p class="lead text-muted">Everything you need for multi-department data management</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-lock feature-icon"></i>
                            <h5 class="fw-bold mb-3">Data Isolation</h5>
                            <p class="text-muted">
                                Each department has isolated data storage. Finance can't see HR data, 
                                and vice versa. Complete privacy guaranteed.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-magic feature-icon"></i>
                            <h5 class="fw-bold mb-3">Smart Mapping</h5>
                            <p class="text-muted">
                                System automatically detects Excel format and maps columns to database. 
                                No manual configuration needed.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-chart-line feature-icon"></i>
                            <h5 class="fw-bold mb-3">Real-time Preview</h5>
                            <p class="text-muted">
                                Preview your data before uploading. See exactly what will be imported 
                                and which columns will be used.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-history feature-icon"></i>
                            <h5 class="fw-bold mb-3">Upload History</h5>
                            <p class="text-muted">
                                Track all uploads with detailed logs. Know who uploaded what, when, 
                                and see success/failure statistics.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-file-csv feature-icon"></i>
                            <h5 class="fw-bold mb-3">Multiple Formats</h5>
                            <p class="text-muted">
                                Support for XLSX, XLS, and CSV files. Each department can have 
                                multiple custom formats.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users-cog feature-icon"></i>
                            <h5 class="fw-bold mb-3">Admin Control</h5>
                            <p class="text-muted">
                                Admins can view all departments, manage users, export master data, 
                                and detect duplicates.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="bg-light py-5" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">How It Works</h2>
                <p class="lead text-muted">Simple 4-step process</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            1
                        </div>
                        <h5 class="fw-bold mb-3">Register & Select Department</h5>
                        <p class="text-muted">Create account and choose your department</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            2
                        </div>
                        <h5 class="fw-bold mb-3">Upload Excel File</h5>
                        <p class="text-muted">Upload your XLSX, XLS, or CSV file</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            3
                        </div>
                        <h5 class="fw-bold mb-3">Preview & Confirm</h5>
                        <p class="text-muted">Review data before final import</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            4
                        </div>
                        <h5 class="fw-bold mb-3">Data Imported</h5>
                        <p class="text-muted">Your data is safely stored in your department</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-3">Ready to Get Started?</h2>
            <p class="lead mb-4">Join hundreds of departments managing their data securely</p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Create Free Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cloud-upload-alt me-2"></i>Upload Center</h5>
                    <p class="text-muted">Multi-Department Data Management System</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} Upload Center. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
