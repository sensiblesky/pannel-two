<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Access denied.');
        }

        $role = auth()->user()->role;

        if ($role !== 'customer') {
            return match ($role) {
                'admin' => redirect('/app/dashboard'),
                'agent' => redirect('/agent/tickets'),
                default => abort(403, 'Access denied.'),
            };
        }

        return $next($request);
    }
}
