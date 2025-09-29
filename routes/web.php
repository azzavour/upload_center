<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelFormatController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Auth;

// Rute untuk autentikasi (Login, Register, dll.)
Auth::routes();

// Halaman utama akan langsung mengarahkan ke halaman upload jika sudah login,
// atau ke halaman login jika belum.
Route::get('/', function () {
    return redirect()->route('upload.index');
})->middleware('auth');

// Grup rute yang hanya bisa diakses setelah login
Route::middleware(['auth'])->group(function () {
    Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload/check', [UploadController::class, 'checkFormat'])->name('upload.check');
    Route::post('/upload/process', [UploadController::class, 'upload'])->name('upload.process'); // FIXED: processUpload -> upload
    
    Route::resource('formats', ExcelFormatController::class);
    Route::resource('mapping', MappingController::class);
    Route::resource('history', HistoryController::class);

    // Default home route dari Laravel, kita arahkan juga ke upload
    Route::get('/home', function () {
        return redirect()->route('upload.index');
    })->name('home');
});