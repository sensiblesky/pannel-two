<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Always update the cache presence (lightweight)
            Cache::put("user_online_{$user->id}", true, 120); // 2 min TTL

            // Always keep last_seen timestamp fresh in cache
            Cache::put("user_last_seen_{$user->id}", now()->toIso8601String(), 300);

            // Throttle DB writes to once per minute
            $dbThrottleKey = "last_seen_db_{$user->id}";
            if (!Cache::has($dbThrottleKey)) {
                Cache::put($dbThrottleKey, true, 60);

                if ($user->role === 'customer') {
                    DB::table('users_customers')
                        ->where('user_id', $user->id)
                        ->update(['last_seen_at' => now()]);
                }
            }
        }

        return $next($request);
    }
}
