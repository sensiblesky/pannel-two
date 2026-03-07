<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Routes that should be accessible even in maintenance mode.
     */
    protected array $except = [
        'login',
        'login/*',
        'logout',
        'config/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (SiteSetting::get('maintenance_mode') !== '1') {
            return $next($request);
        }

        // Check if scheduled end time has passed
        $endAt = SiteSetting::get('maintenance_end_at');
        if ($endAt && Carbon::parse($endAt)->isPast()) {
            SiteSetting::set('maintenance_mode', '0');
            SiteSetting::set('maintenance_end_at', null);
            return $next($request);
        }

        // Allow authenticated admins through
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Allow excepted routes
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        return response()->view('errors.503', [
            'maintenanceEndAt' => $endAt,
        ], 503);
    }
}
