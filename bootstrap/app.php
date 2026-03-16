<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.student' => \App\Http\Middleware\AuthStudent::class,
            'auth.validator' => \App\Http\Middleware\EnsureUserIsValidator::class,
            'auth.admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'auth.any' => \App\Http\Middleware\AuthenticateAny::class,
            // Multi-role middleware
            'multi.role' => \App\Http\Middleware\CheckMultiRole::class,
            'operator.level' => \App\Http\Middleware\CheckOperatorLevel::class,
            'pimpinan.level' => \App\Http\Middleware\CheckPimpinanLevel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
