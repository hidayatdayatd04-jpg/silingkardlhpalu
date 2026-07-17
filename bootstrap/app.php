<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackWebsiteVisit;
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
            'admin.access' => EnsureAdminPanelAccess::class,
            'track.visit' => TrackWebsiteVisit::class,
        ]);
        $middleware->appendToGroup('web', TrackWebsiteVisit::class);
        $middleware->appendToGroup('web', SetLocale::class);
        
        // Exclude webhook endpoints from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
