<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ShelterController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1',
    'middleware' => 'api',
    ], function () {
    Route::group(['prefix' => 'admin', 'middleware' => JwtMiddleware::class], function () {
        Route::get('/users', [AdminController::class, 'getUser']);
        Route::get('/shelters', [AdminController::class, 'getShelter']);
    });
    Route::group(['prefix' => 'auth'], function () {
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

    Route::group(['prefix' => 'shelter'], function () {
        Route::post('/register', [ShelterController::class, 'createShelter']);
        Route::put('/update', [ShelterController::class, 'updateShelter'])->middleware(JwtMiddleware::class);
        Route::group(['prefix' => 'pet'], function () {
            Route::post('/add', [PetController::class, 'addPet'])->middleware(JwtMiddleware::class);
        });
    });
});

