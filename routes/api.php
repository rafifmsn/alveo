<?php

use App\Http\Controllers\WindowFinderController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/locations', [WindowFinderController::class, 'searchLocations'])
    ->name('api.locations.search');

Route::post('/v1/evaluate-window', [WindowFinderController::class, 'evaluateApi'])
    ->name('api.evaluate-window');
