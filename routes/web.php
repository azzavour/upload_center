<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelFormatController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\HistoryController;

Route::get('/', function () {
    return redirect()->route('upload.index');
});

// Excel Format Routes
Route::prefix('formats')->name('formats.')->group(function () {
    Route::get('/', [ExcelFormatController::class, 'index'])->name('index');
    Route::get('/create', [ExcelFormatController::class, 'create'])->name('create');
    Route::post('/', [ExcelFormatController::class, 'store'])->name('store');
});

// Upload Routes
Route::prefix('upload')->name('upload.')->group(function () {
    Route::get('/', [UploadController::class, 'index'])->name('index');
    Route::post('/check-format', [UploadController::class, 'checkFormat'])->name('check');
    Route::post('/process', [UploadController::class, 'upload'])->name('process');
});

// Mapping Routes
Route::prefix('mapping')->name('mapping.')->group(function () {
    Route::get('/', [MappingController::class, 'index'])->name('index');
    Route::get('/create', [MappingController::class, 'create'])->name('create');
    Route::post('/', [MappingController::class, 'store']);
    Route::post('/', [MappingController::class, 'store'])->name('store');
    Route::get('/{id}', [MappingController::class, 'show'])->name('show');
});

Route::prefix('history')->name('history.')->group(function () {
    Route::get('/', [HistoryController::class, 'index'])->name('index');
    Route::get('/{id}', [HistoryController::class, 'show'])->name('show');
});
Route::delete('/mapping/{id}', [MappingController::class, 'destroy'])->name('mapping.destroy');