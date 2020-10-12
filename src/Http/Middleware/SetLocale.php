<?php

namespace mNic\LaraLoc\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $laraLocService = laraloc();

        $locale = $request->route()->parameter(
            $laraLocService->getLocaleKey(),
            $laraLocService->getFallBackLocale()
        );

        URL::defaults([
            $laraLocService->getLocaleKey() => $locale
        ]);

        $request->route()->forgetParameter(
            $laraLocService->getLocaleKey()
        );

        $laraLocService->setAppLocale($locale);

        return $next($request);
    }
}
