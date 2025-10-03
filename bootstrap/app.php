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
            'auth.basic' => \App\Http\Middleware\HttpBasicAuth::class,
        ]);
        
        // Add traffic source tracking to all web requests
        $middleware->web([
            \App\Http\Middleware\TrackTrafficSource::class,
        ]);
        
        // Add trusted proxies middleware for production
        $middleware->append(\Illuminate\Http\Middleware\TrustProxies::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
