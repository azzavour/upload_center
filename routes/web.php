<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ExcelFormatController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AdminMasterController;
use App\Http\Controllers\DepartmentUploadController;
use App\Http\Controllers\Admin\UserActivityController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Format Excel Routes
    Route::prefix('formats')->name('formats.')->group(function () {
        Route::get('/', [ExcelFormatController::class, 'index'])->name('index');
        Route::get('/create', [ExcelFormatController::class, 'create'])->name('create');
        Route::post('/', [ExcelFormatController::class, 'store'])->name('store');
    });

    // ✅ GANTI: Department Upload Routes (dulu my-uploads)
    Route::prefix('department-uploads')->name('department-uploads.')->group(function () {
        Route::get('/', [DepartmentUploadController::class, 'index'])->name('index');
        Route::get('/stats', [DepartmentUploadController::class, 'stats'])->name('stats');
        Route::get('/download/{id}', [DepartmentUploadController::class, 'download'])->name('download');
    });

    // Upload Routes
    Route::prefix('upload')->name('upload.')->group(function () {
        Route::get('/', [UploadController::class, 'index'])->name('index');
        Route::post('/check', [UploadController::class, 'checkFormat'])->name('check');
        Route::post('/process', [UploadController::class, 'upload'])->name('process');
    });

    // Mapping Routes
    Route::prefix('mapping')->name('mapping.')->group(function () {
        Route::get('/', [MappingController::class, 'index'])->name('index');
        Route::get('/create', [MappingController::class, 'create'])->name('create');
        Route::post('/', [MappingController::class, 'store'])->name('store');
        Route::get('/{id}', [MappingController::class, 'show'])->name('show');
        Route::delete('/{id}', [MappingController::class, 'destroy'])->name('destroy');
    });

    // History Routes
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        Route::get('/{id}', [HistoryController::class, 'show'])->name('show');
    });
});

// Admin Only Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Department Management
    Route::resource('departments', DepartmentController::class);

    // User Activity Monitoring
    Route::prefix('user-activity')->name('user-activity.')->group(function () {
        Route::get('/', [UserActivityController::class, 'index'])->name('index');
        Route::get('/{userId}', [UserActivityController::class, 'show'])->name('show');
        Route::get('/{userId}/export', [UserActivityController::class, 'export'])->name('export');
    });

    // Master Data
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/', [AdminMasterController::class, 'index'])->name('index');
        Route::get('/export', [AdminMasterController::class, 'export'])->name('export');
        Route::get('/duplicates', [AdminMasterController::class, 'detectDuplicates'])->name('duplicates');
    });

    // All Uploads (Admin View)
    Route::get('/all-uploads', [AdminMasterController::class, 'allUploads'])->name('all-uploads');
});