<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Retrieve the locale from the session or fallback to the default locale
        $locale = Session::get('locale', config('app.locale'));

        // Set the application's locale
        App::setLocale($locale);

        return $next($request);
    }
}
