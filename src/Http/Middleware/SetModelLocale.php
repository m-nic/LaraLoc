<?php

namespace mNic\LaraLoc\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetModelLocale
{
    public function handle(Request $request, Closure $next)
    {
        $laraLocService = laraloc();

        $modelLocaleKey = $laraLocService->getModelLocaleKey();

        if ($lmlang = $request->query($modelLocaleKey)) {
            $laraLocService->setModelLocale($lmlang);

            return redirect()->to(
                $laraLocService->makeRedirectUrlForLocale(
                    $request,
                    null,
                    [$laraLocService->getModelLocaleKey()]
                )
            );
        }

        if (session()->has($modelLocaleKey)) {
            $request->merge([$modelLocaleKey => true]);
        }

        return $next($request);
    }
}
