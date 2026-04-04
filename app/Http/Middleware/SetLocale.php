<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['id', 'en'];
        $locale = session('locale');

        if (! $locale) {
            $browserLocale = substr((string) $request->server('HTTP_ACCEPT_LANGUAGE', 'id'), 0, 2);
            $locale = in_array($browserLocale, $supportedLocales, true) ? $browserLocale : 'id';
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
