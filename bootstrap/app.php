<?php

use App\Http\Middleware\EnsureAccountVerified;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAthletePage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'verified.account' => EnsureAccountVerified::class,
            'admin' => EnsureAdmin::class,
            'athlete.page' => EnsureAthletePage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
