<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'tr'));
        $supportedLocales = ['tr', 'en'];

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale', 'tr');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
