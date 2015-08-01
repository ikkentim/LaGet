<?php

namespace Laget\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Laget\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Laget\Http\Middleware\DebugRequest::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'       => \Laget\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest'      => \Laget\Http\Middleware\RedirectIfAuthenticated::class,
        'auth.nuget' => \Laget\Http\Middleware\NugetAuthenticate::class,
        'file.nuget' => \Laget\Http\Middleware\NugetFile::class,
        'csrf'       => \Laget\Http\Middleware\VerifyCsrfToken::class,
    ];
}
