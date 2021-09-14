<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;

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

    // Email verification routes
    Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
});



