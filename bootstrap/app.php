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
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->redirectTo(
            guests: '/login',
            users: function () {
                if (auth()->check() && auth()->user()->role === 'staff_gudang') {
                    return route('inbound.index', absolute: false);
                }
                return route('dashboard', absolute: false);
            }
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('logout') || $request->is('logout*')) {
                if (auth()->check()) {
                    auth()->logout();
                }
                return redirect()->route('login');
            }
        });
    })->create();
