<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['tr', 'en'];

        $locale = config('app.locale', 'tr');

        if (Schema::hasTable('settings')) {
            $configuredLocale = (string) Setting::get('admin_locale', $locale);
            if (in_array($configuredLocale, $supportedLocales, true)) {
                $locale = $configuredLocale;
            }
        }

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = 'tr';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
