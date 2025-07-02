<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ARCalc\ProductEntryController;
use Illuminate\Support\Facades\Auth;

// Public view
Route::get('/', function () {
    return view('gts');
})->name('home');

// Admin Dashboard Route (protected)
Route::get('/admin-dashboard', function () {
    return view('gts');
})->middleware(['auth'])->name('admin.dashboard');

// Protected ARCalc routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/revenue-calculator', [ProductEntryController::class, 'view'])->name('calculator.index');
    Route::post('/handle-data', [ProductEntryController::class, 'store']);
    Route::get('/get-all-entries', [ProductEntryController::class, 'getAllEntries']);
    Route::delete('/delete-entry/{id}', [ProductEntryController::class, 'destroy'])->name('delete-entry');
    Route::post('/update-entry/{id}', [ProductEntryController::class, 'update']);
});

Route::get('/revenue-calculator', [ProductEntryController::class, 'view'])
    ->middleware(['auth.redirect'])
    ->name('calculator.index');

// Auth routes
require __DIR__ . '/auth.php';

Route::get('/amazon-services', function () {
    return view('amazon-services');
});

Route::get('/modern-admin-login', function () {
    return view('auth.modern-admin-login');
})->name('modern.login');