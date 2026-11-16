<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('assets', AssetController::class);

    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store']);
    Route::patch('/loans/{loan}/return', [LoanController::class, 'markReturned'])->name('loans.return');

    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('departments', DepartmentController::class)->except('show');
    });
});

require __DIR__.'/auth.php';
