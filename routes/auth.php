<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// ─── Guest routes (unauthenticated only) ─────────────────────────────
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')  // 5 attempts per minute
        ->name('login.store');

    // Password recovery
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:3,5')  // 3 attempts per 5 minutes
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.update');
});

// ─── Authenticated routes ────────────────────────────────────────────
Route::middleware(['auth', 'account.active', 'session.timeout', 'prevent.back.history'])->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // 2FA challenge (must come before two-factor middleware is applied)
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])
        ->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])
        ->name('two-factor.verify');

    // Routes requiring 2FA to be verified
    Route::middleware('two-factor')->group(function () {
        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])
            ->name('profile.show');
        Route::patch('/profile/name', [ProfileController::class, 'updateName'])
            ->name('profile.update-name');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.update-password');

        // 2FA setup (within authenticated + 2fa-verified context)
        Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])
            ->name('two-factor.setup');
        Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
            ->name('two-factor.enable');
        Route::delete('/two-factor/disable', [TwoFactorController::class, 'disable'])
            ->name('two-factor.disable');
    });
});
