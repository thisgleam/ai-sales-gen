<?php

use App\Http\Controllers\SalesPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [SalesPageController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/create',  [SalesPageController::class, 'create'])->name('pages.create');
    Route::post('/dashboard/create', [SalesPageController::class, 'store'])->name('pages.store');

    Route::post('/pages/{salesPage}/regenerate', [SalesPageController::class, 'regenerate'])->name('pages.regenerate');
    Route::post('/pages/{salesPage}/regenerate-section', [SalesPageController::class, 'regenerateSection'])->name('pages.regenerate-section');
    Route::post('/pages/{salesPage}/style',           [SalesPageController::class, 'updateStyle'])->name('pages.update-style');
    Route::get('/pages/{salesPage}/export',   [SalesPageController::class, 'export'])->name('pages.export');
    Route::delete('/pages/{salesPage}',       [SalesPageController::class, 'destroy'])->name('pages.destroy');
});

Route::get('/pages/{salesPage}', [SalesPageController::class, 'show'])->name('pages.show');
