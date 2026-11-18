<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/assets/trashed', [AssetController::class, 'trashed'])->name('assets.trashed');
    Route::patch('/assets/{asset}/restore', [AssetController::class, 'restore'])
        ->withTrashed()->name('assets.restore');
    Route::delete('/assets/{asset}/force', [AssetController::class, 'forceDestroy'])
        ->withTrashed()->name('assets.force-destroy');

Route::resource('assets', AssetController::class)->withTrashed(['show']);

    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store']);
    Route::patch('/loans/{loan}/return', [LoanController::class, 'markReturned'])->name('loans.return');

    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('departments', DepartmentController::class)->except('show');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/assets', [ReportController::class, 'assets'])->name('assets');
            Route::get('/assets/csv', [ReportController::class, 'assetsCsv'])->name('assets.csv');
            Route::get('/loans', [ReportController::class, 'loans'])->name('loans');
            Route::get('/loans/csv', [ReportController::class, 'loansCsv'])->name('loans.csv');
        });
    });
});

require __DIR__.'/auth.php';
