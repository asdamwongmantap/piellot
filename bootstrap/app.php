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
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware Inertia — wajib jalan di setiap request web supaya
        // setiap halaman Vue menerima data lewat Inertia::render().
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Alias middleware khusus PIELLOT — dipakai di routes/web.php
        // (Route::middleware('admin') / 'pic' / 'active').
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'pic' => \App\Http\Middleware\EnsurePic::class,
            'active' => \App\Http\Middleware\EnsureActiveAccount::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
