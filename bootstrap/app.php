<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/app.php'));
            Route::middleware('web')->group(base_path('routes/customer.php'));
            Route::middleware('web')->group(base_path('routes/agent.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->append(\App\Http\Middleware\TrackLastSeen::class);

        $middleware->alias([
            'admin.agent' => \App\Http\Middleware\AdminAgentMiddleware::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'agent' => \App\Http\Middleware\AgentMiddleware::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            if ($user && $user->role === 'customer') {
                return route('customer.dashboard');
            }
            if ($user && $user->role === 'admin') {
                return route('dashboard');
            }
            if ($user && $user->role === 'agent') {
                return route('agent.tickets/dashboard');
            }
            return route('customer.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
