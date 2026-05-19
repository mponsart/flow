<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\FinanceController;

Route::middleware(['auth:sanctum', 'restrict.domain'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::get('/finances/kpi', [FinanceController::class, 'kpi']);
    Route::get('/finances/forecast', [FinanceController::class, 'forecast']);
});
