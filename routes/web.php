<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/services', [LandingPageController::class, 'services'])->name('services');
Route::get('/agencies', [LandingPageController::class, 'agencies'])->name('agencies');
Route::get('/executive', [LandingPageController::class, 'executive'])->name('executive');
Route::get('/legislative', [LandingPageController::class, 'legislative'])->name('legislative');
Route::get('/judiciary', [LandingPageController::class, 'judiciary'])->name('judiciary');

Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

// Admin Auth (only accessible from admin devices)
Route::middleware('admin.device')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'sendOtp'])->name('admin.login.send');
    Route::get('/admin/verify', [AuthController::class, 'showVerify'])->name('admin.verify');
    Route::post('/admin/verify', [AuthController::class, 'verifyOtp'])->name('admin.verify.submit');
});
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Panel (protected by auth + admin check)
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    // Wallet Routes
    Route::get('/wallet-transactions', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet-transactions', [WalletController::class, 'store'])
        ->middleware('throttle:5,1')  // Rate limiting: 5 requests per minute
        ->name('wallet.store');
    Route::middleware('can:createStaff,App\\Models\\User')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });

    Route::middleware('can:manageContent,App\\Models\\User')->group(function () {
        Route::get('/{type}', [AdminController::class, 'index'])->name('resource.index');
        Route::get('/{type}/create', [AdminController::class, 'create'])->name('resource.create');
        Route::post('/{type}', [AdminController::class, 'store'])->name('resource.store');
        Route::get('/{type}/{id}/edit', [AdminController::class, 'edit'])->name('resource.edit');
        Route::put('/{type}/{id}', [AdminController::class, 'update'])->name('resource.update');
        Route::delete('/{type}/{id}', [AdminController::class, 'destroy'])->name('resource.destroy');
    });
});
