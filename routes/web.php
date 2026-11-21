<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubBroadcastController;
use App\Http\Controllers\DosimetryController;
use App\Http\Controllers\ExposureHistoryController;
use App\Http\Controllers\SavedRouteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WindowFinderController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/verify', [AuthController::class, 'showVerify'])->name('verification.notice');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'overview'])->name('overview');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/system', [AdminController::class, 'system'])->name('system');
        Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear-cache');
        Route::post('/system/reset', [AdminController::class, 'resetDemo'])->name('system.reset');
    });

Route::middleware(['auth', 'verified.account', 'athlete.page'])->group(function () {
    Route::get('/finder', [WindowFinderController::class, 'index'])->name('finder');
    Route::get('/calendar/ics', [WindowFinderController::class, 'downloadIcs'])->name('calendar.ics');

    Route::get('/routes', [SavedRouteController::class, 'index'])->name('routes.index');
    Route::post('/routes', [SavedRouteController::class, 'store'])->name('routes.store');
    Route::delete('/routes/{route}', [SavedRouteController::class, 'destroy'])->name('routes.destroy');

    Route::get('/dosimetry', [DosimetryController::class, 'index'])->name('dosimetry.index');

    Route::get('/history', [ExposureHistoryController::class, 'index'])->name('history.index');
    Route::post('/history', [ExposureHistoryController::class, 'store'])->name('history.store');
    Route::delete('/history/{workout}', [ExposureHistoryController::class, 'destroy'])->name('history.destroy');

    Route::get('/broadcasts', [ClubBroadcastController::class, 'index'])->name('broadcasts.index');
    Route::post('/broadcasts', [ClubBroadcastController::class, 'store'])->name('broadcasts.store');
    Route::post('/broadcasts/{broadcast}/follow', [ClubBroadcastController::class, 'toggleFollow'])->name('broadcasts.follow');
    Route::delete('/broadcasts/{broadcast}', [ClubBroadcastController::class, 'destroy'])->name('broadcasts.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
