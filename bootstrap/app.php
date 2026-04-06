<?php

use App\Http\Middleware\CheckSuperAdmin;
use App\Http\Middleware\CheckUserAccessStatus;
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

        $middleware->statefulApi();
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->alias([
            'company.access' => \App\Http\Middleware\CheckCompanyAccess::class,
            'super_admin' => CheckSuperAdmin::class,
            'check.user.status' => CheckUserAccessStatus::class,

        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*', // Disable CSRF for API routes
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
