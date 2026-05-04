<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests for HTML pages (not assets, API, etc.)
        if ($request->isMethod('GET') && !$request->ajax() && !$request->is('cms/*', 'api/*', 'login', 'register')) {
            try {
                PageView::create([
                    'user_id' => $request->user()?->id,
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'viewed_date' => now('Asia/Jakarta')->toDateString(),
                ]);
            } catch (\Throwable $e) {
                // Silently fail - don't break the page for tracking issues
            }
        }

        return $response;
    }
}
