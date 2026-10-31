<?php

use App\Http\Controllers\WindowFinderController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/evaluate-window', [WindowFinderController::class, 'evaluateApi'])
    ->name('api.evaluate-window');
