<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'redirectToGoogle'])->name('login');
Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('revenues', RevenueController::class);
    Route::resource('expenses', ExpenseController::class);

    // AI
    Route::get('ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('ai/summary', [AiController::class, 'summary'])->name('ai.summary');
    Route::post('ai/analysis', [AiController::class, 'analysis'])->name('ai.analysis');
    Route::post('ai/anomalies', [AiController::class, 'anomalies'])->name('ai.anomalies');
    Route::get('ai/{report}', [AiController::class, 'show'])->name('ai.show');

    // Forecasts
    Route::get('forecasts', [ForecastController::class, 'index'])->name('forecasts.index');
    Route::post('forecasts/generate', [ForecastController::class, 'generate'])->name('forecasts.generate');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'downloadPDF'])->name('reports.pdf');
});
