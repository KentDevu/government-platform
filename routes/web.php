<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/services', [LandingPageController::class, 'services'])->name('services');
Route::get('/agencies', [LandingPageController::class, 'agencies'])->name('agencies');
Route::get('/executive', [LandingPageController::class, 'executive'])->name('executive');
Route::get('/legislative', [LandingPageController::class, 'legislative'])->name('legislative');
Route::get('/judiciary', [LandingPageController::class, 'judiciary'])->name('judiciary');

// Admin Auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Panel (protected)
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/{type}', [AdminController::class, 'index'])->name('resource.index');
    Route::get('/{type}/create', [AdminController::class, 'create'])->name('resource.create');
    Route::post('/{type}', [AdminController::class, 'store'])->name('resource.store');
    Route::get('/{type}/{id}/edit', [AdminController::class, 'edit'])->name('resource.edit');
    Route::put('/{type}/{id}', [AdminController::class, 'update'])->name('resource.update');
    Route::delete('/{type}/{id}', [AdminController::class, 'destroy'])->name('resource.destroy');
});
