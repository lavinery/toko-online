<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias yang benar
        $middleware->alias([
            'role'     => \App\Http\Middleware\RoleMiddleware::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'log.api' => \App\Http\Middleware\LogApiRequests::class,
        ]);

        // (Opsional tapi bagus) tambahkan throttle default ke grup 'api'
        $middleware->appendToGroup('api', [
            'force.json',
            'log.api',
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
