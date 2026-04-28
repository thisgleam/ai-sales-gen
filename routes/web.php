<?php

use App\Http\Controllers\SalesPageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [SalesPageController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/create', [SalesPageController::class, 'create'])->name('pages.create');
    Route::post('/dashboard/create', [SalesPageController::class, 'store'])->middleware('throttle:ai-generation')->name('pages.store');
    Route::post('/dashboard/create/stream', [SalesPageController::class, 'storeStream'])->middleware('throttle:ai-generation')->name('pages.store-stream');

    Route::post('/pages/{salesPage}/regenerate', [SalesPageController::class, 'regenerate'])->middleware('throttle:ai-generation')->name('pages.regenerate');
    Route::post('/pages/{salesPage}/regenerate-section', [SalesPageController::class, 'regenerateSection'])->name('pages.regenerate-section');
    Route::post('/pages/{salesPage}/update-section', [SalesPageController::class, 'updateSection'])->name('pages.update-section');
    Route::post('/pages/{salesPage}/reorder', [SalesPageController::class, 'reorder'])->name('pages.reorder');
    Route::post('/pages/{salesPage}/style', [SalesPageController::class, 'updateStyle'])->name('pages.update-style');
    Route::post('/pages/{salesPage}/font-pair', [SalesPageController::class, 'updateFontPair'])->name('pages.update-font-pair');
    Route::get('/pages/{salesPage}/export', [SalesPageController::class, 'export'])->name('pages.export');
    Route::delete('/pages/{salesPage}', [SalesPageController::class, 'destroy'])->name('pages.destroy');
});

Route::get('/pages/{salesPage}', [SalesPageController::class, 'show'])->name('pages.show');
