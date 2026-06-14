<?php

use App\Http\Middleware\AliceAudit;
use App\Http\Middleware\AliceAuthenticate;
use App\Http\Middleware\AliceDryRun;
use App\Http\Middleware\AliceIdempotency;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetLocale;
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
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeadersMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'alice.auth' => AliceAuthenticate::class,
            'alice.audit' => AliceAudit::class,
            'alice.idempotency' => AliceIdempotency::class,
            'alice.dryrun' => AliceDryRun::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
