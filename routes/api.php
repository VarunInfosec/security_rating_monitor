<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\VendorSubscriptionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\VendorController;

Route::prefix('srm')->group(function() {

    // User Authentication routes
    Route::middleware(['guest'])->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::post('/login', [LoginController::class, 'authenticate']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
    });

    Route::get('/get-domain/{id}', [DomainController::class, 'show']);

    Route::post('/subscribe-vendor', [VendorSubscriptionController::class, 'store'])
            ->middleware(['auth', 'verified']);
    Route::get('/get-vendors', [VendorController::class, 'show'])
            ->middleware(['auth', 'verified']);

    // Email verification routes
    Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
});



