<?php

// bootstrap/app.php (Alternative without throttle for now)
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
        // Middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'log.api' => \App\Http\Middleware\LogApiRequests::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
        ]);

        // API middleware group (tanpa throttle dulu)
        $middleware->group('api', [
            'cors',
            'force.json',
            'log.api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
