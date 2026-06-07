<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ForecastController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'redirectToGoogle'])->name('login');
Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'restrict.domain'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::resource('projects', ProjectController::class);

    Route::get('revenues', [RevenueController::class, 'index'])->name('revenues.index');
    Route::get('revenues/{year}/{month}/edit', [RevenueController::class, 'edit'])->name('revenues.edit');
    Route::put('revenues/{year}/{month}', [RevenueController::class, 'update'])->name('revenues.update');

    Route::resource('expenses', ExpenseController::class)->except(['show']);
    Route::get('expenses/{year}/{month}/override', [ExpenseController::class, 'override'])->name('expenses.override');
    Route::post('expenses/{year}/{month}/override', [ExpenseController::class, 'storeOverride'])->name('expenses.storeOverride');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('forecasts', [ForecastController::class, 'index'])->name('forecasts.index');
});
