<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en-IN', 'ur-IN', 'en', 'ur'];

        $locale = $request->header('X-Locale')
            ?? $request->cookie('ht_locale')
            ?? $request->get('locale')
            ?? $request->getPreferredLanguage($supported)
            ?? config('app.locale', 'en-IN');

        if (str_starts_with($locale, 'ur')) {
            $normalized = 'ur-IN';
        } else {
            $normalized = 'en-IN';
        }

        app()->setLocale($normalized);

        $response = $next($request);

        return $response;
    }
}
