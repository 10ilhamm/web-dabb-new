<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TrackOnlineUsers
{
    /**
     * Track online users by storing last activity timestamp per user in cache.
     * Online = users with activity in the last 5 minutes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $userId = $request->user()->id;
            $cacheKey = "online_user_{$userId}";
            // Store timestamp of last activity
            Cache::put($cacheKey, now('Asia/Jakarta')->timestamp, now('Asia/Jakarta')->addMinutes(10));
        }

        return $next($request);
    }
}