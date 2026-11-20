<?php

use App\Http\Middleware\BlockEmojis;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\RoleMiddleware;
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
        $middleware->web(append: [
            BlockEmojis::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('logout') || $request->routeIs('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }

            return redirect()->route('login')->with('info', 'Your session has expired. Please log in again.');
        });
    })->create();