<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ARCalc\ProductEntryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\FedexTrackingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DocumentHubController;
use Illuminate\Support\Facades\Auth;

// Public view
Route::get('/', function () {
    return view('gts');
})->name('home');

Route::get('/login', function () {
    return redirect('/?login=1');
})->middleware('guest'); // no route name here so we don't collide with auth.php's named 'login'

// Route::middleware(['auth'])->get('/admin-dashboard', fn() => view('admin.dashboard'))
//     ->name('admin.dashboard');

Route::middleware(['auth', 'admin'])
    ->get('/admin-dashboard', DashboardController::class)
    ->name('admin.dashboard');

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
})->name('amazon.services');

Route::get('/modern-admin-login', function () {
    return view('auth.modern-admin-login');
})->name('modern.login');

Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.submit')
    ->middleware('throttle:3,1'); // at most 3 per minute

Route::post('/account/password', [AccountController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('account.password');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])
    ->name('newsletter.subscribe');

Route::middleware(['auth', 'admin'])
    ->get('/admin/leads', [LeadsController::class, 'index'])
    ->name('leads.index');


Route::get('/fedex/track', [FedexTrackingController::class, 'showForm'])
    ->name('fedex.track.form');

Route::post('/fedex/track', [FedexTrackingController::class, 'track'])
    ->name('fedex.track.submit');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/document-hub', [DocumentHubController::class, 'index'])
        ->name('dh.index');

    Route::post('/document-hub/folders', [DocumentHubController::class, 'storeFolder'])
        ->name('dh.folders.store');

    Route::delete('/document-hub/folders/{folder}', [DocumentHubController::class, 'destroy'])
        ->name('dh.folders.destroy');

    Route::get('/document-hub/{folder}/subfolders', [DocumentHubController::class, 'subfolderIndex'])
        ->name('dh.subfolders.index');

    Route::get('/document-hub/{folder}', [DocumentHubController::class, 'show'])
        ->name('dh.show');

    Route::post('/document-hub/{folder}/records', [DocumentHubController::class, 'storeRecord'])
        ->name('dh.records.store');

    Route::post('/document-hub/records/{record}/upload', [DocumentHubController::class, 'uploadFile'])
        ->name('dh.records.upload');

    Route::get('/document-hub/records/{record}/download', [DocumentHubController::class, 'download'])
        ->name('dh.records.download');

    Route::delete('/document-hub/records/{record}', [DocumentHubController::class, 'destroyRecord'])
        ->name('dh.records.destroy');

    Route::get('/document-hub/{folder}/download-all', [DocumentHubController::class, 'downloadAll'])
        ->name('dh.folder.downloadAll');
});
