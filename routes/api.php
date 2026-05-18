<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\API\CollectionController;
use App\Http\Controllers\API\DashboardController;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | COMMON
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [AuthController::class, 'profile']);

    /*
    |--------------------------------------------------------------------------
    | COLLECTION MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/collections', [CollectionController::class, 'index']);

    Route::post('/collections', [CollectionController::class, 'store']);

    Route::get('/collections/{id}', [CollectionController::class, 'show']);

    Route::prefix('dashboard')->group(function () {

    Route::get('/summary', [
        DashboardController::class,
        'summary'
    ]);

    Route::get('/payment-mode', [
        DashboardController::class,
        'paymentModeCollection'
    ]);

    Route::get('/monthly-trend', [
        DashboardController::class,
        'monthlyTrend'
    ]);

    Route::get('/agent-performance', [
        DashboardController::class,
        'agentPerformance'
    ]);

    Route::get('/best-time-slot', [
        DashboardController::class,
        'bestTimeSlot'
    ]);
});

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        // Register User
        Route::post('/register', [AuthController::class, 'register']);

        // Change Role
        Route::post('/change-role', [AuthController::class, 'changeRole']);

        /*
    |--------------------------------------------------------------------------
    | LOAN MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/loans', [LoanController::class, 'index']);

    Route::post('/loans', [LoanController::class, 'store']);

    Route::get('/loans/{id}', [LoanController::class, 'show']);

    Route::post('/loans/{id}', [LoanController::class, 'update']);

    Route::delete('/loans/{id}', [LoanController::class, 'destroy']);
    });
});

