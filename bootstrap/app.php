<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        // Apply CSP headers on every response so the portal can iframe this module
        $middleware->append(\App\Http\Middleware\ModuleCspMiddleware::class);

        // Exempt SSO exchange from CSRF — the short-lived SSO token is the proof of identity
        $middleware->validateCsrfTokens(except: [
            '/sso/exchange',
            '/api/*',
        ]);

        // Named middleware aliases
        $middleware->alias([
            'sso.session' => \App\Http\Middleware\RequireSsoSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for API routes on any unhandled exception
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->is('sso/exchange')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'error'   => $e->getMessage() ?: 'Server error.',
                    'status'  => $status,
                ], $status);
            }
        });
    })->create();
