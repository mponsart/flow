<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use Illuminate\Support\Facades\Auth;
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'redirectToGoogle'])->name('login');
Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
