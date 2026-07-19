<?php

use App\Http\Middleware\ScopeToOrganization;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy in front of us (e.g. Cloudflare Tunnel) so
        // the request's detected scheme/host honor X-Forwarded-* headers -
        // without this, signed URL validation reconstructs the comparison
        // URL from the origin's own (wrong) scheme and always 403s.
        $middleware->trustProxies(at: '*');

        // Stream's payment webhook can't send a Laravel CSRF token; it's
        // authenticated instead via its own HMAC signature header, verified
        // in ReportPaymentController::webhook().
        $middleware->validateCsrfTokens(except: ['webhooks/streampay']);

        $middleware->alias([
            'scope.organization' => ScopeToOrganization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
