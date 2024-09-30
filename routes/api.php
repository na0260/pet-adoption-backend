<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login');
        Route::group(['middleware' => JwtMiddleware::class], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });
    Route::post('/send-otp', [PasswordResetController::class, 'sendOTP'])->middleware('throttle:3,1');
    Route::post('/validate-otp', [PasswordResetController::class, 'validateOTP']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

