<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return tap(
    Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            $middleware->web(append: [
                \App\Http\Middleware\SetLocale::class,
                \App\Http\Middleware\EnsureActiveUser::class,
            ]);
        })
        ->withExceptions(function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
            );
        })->create(),
    function (Application $app): void {
        /*
         * The Freebuff workspace injects environment variables directly into the
         * process, so there is no local .env file. Point Dotenv at the harmless
         * example file so booting never warns about a missing file — the loader
         * is immutable, so the injected environment always wins.
         */
        if (! is_file(base_path('.env'))) {
            $app->loadEnvironmentFrom('.env.example');
        }
    },
);
