<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\AgencyController;
use App\Http\Controllers\Api\V1\ExecutiveController;
use App\Http\Controllers\Api\V1\JudiciaryController;
use App\Http\Controllers\Api\V1\LandingController;
use App\Http\Controllers\Api\V1\LegislativeController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProfileController;
use App\Http\Controllers\Api\V1\Admin\ResourceController;
use App\Http\Controllers\Api\V1\Admin\StaffController;
use App\Http\Controllers\Api\V1\Admin\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

// ── Public endpoints (no auth) ──────────────────────────────────────────
Route::get('/landing', [LandingController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/agencies', [AgencyController::class, 'index']);
Route::get('/executive', [ExecutiveController::class, 'index']);
Route::get('/legislative', [LegislativeController::class, 'index']);
Route::get('/judiciary', [JudiciaryController::class, 'index']);

// ── Auth endpoints (rate-limited) ───────────────────────────────────────
Route::middleware('throttle:api-auth')->prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
});

// ── Protected endpoints (Sanctum token required) ────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile', [ProfileController::class, 'update']); // multipart/form-data fallback

    // Admin-only routes
    Route::middleware('api.admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Wallet transactions
        Route::get('/wallet-transactions', [WalletController::class, 'index']);
        Route::post('/wallet-transactions', [WalletController::class, 'store']);

        // Staff management (requires createStaff ability)
        Route::middleware('can:createStaff,App\\Models\\User')->group(function () {
            Route::get('/staff', [StaffController::class, 'index']);
            Route::post('/staff', [StaffController::class, 'store']);
            Route::get('/staff/{staff}', [StaffController::class, 'show']);
            Route::put('/staff/{staff}', [StaffController::class, 'update']);
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
        });

        // Resource CRUD (requires manageContent ability) — prefixed to avoid public route shadowing
        Route::middleware('can:manageContent,App\\Models\\User')->prefix('resources')->group(function () {
            Route::get('/{type}', [ResourceController::class, 'index']);
            Route::post('/{type}', [ResourceController::class, 'store']);
            Route::get('/{type}/{id}', [ResourceController::class, 'show']);
            Route::put('/{type}/{id}', [ResourceController::class, 'update']);
            Route::post('/{type}/{id}', [ResourceController::class, 'update']); // multipart/form-data fallback
            Route::delete('/{type}/{id}', [ResourceController::class, 'destroy']);
        });
    });
});
