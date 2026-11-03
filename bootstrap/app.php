
<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    ->withMiddleware(function (Middleware $middleware): void {
        //
        // Konfigurasi middleware tambahan dapat ditempatkan di sini.
        //
        $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    */

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Format JSON untuk request API
        |--------------------------------------------------------------------------
        */

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool =>
                $request->is('api/*')
                || $request->expectsJson(),
        );

        /*
        |--------------------------------------------------------------------------
        | Model tidak ditemukan
        |--------------------------------------------------------------------------
        */

        
$exceptions->render(
    function (
        NotFoundHttpException $exception,
        Request $request
    ): ?JsonResponse {
        if (! $request->is('api/*')) {
            return null;
        }

        // Pastikan 404 berasal dari model yang tidak ditemukan.
        if (! (
            $exception->getPrevious()
                instanceof ModelNotFoundException
        )) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Data yang diminta tidak ditemukan',
        ], 404);
    }
);


    })

    /*
    |--------------------------------------------------------------------------
    | Create Application
    |--------------------------------------------------------------------------
    */

    ->create();
