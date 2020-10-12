<?php

namespace mNic\LaraLoc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LaraLocService
{
    private $fallbackRegistered = false;

    public function hasRegisteredFallbackRoute()
    {
        return $this->fallbackRegistered;
    }

    public function enableTranslationForGroup(\Closure $closure)
    {
        Route::group([
            'prefix'     => '{' . $this->getLocaleKey() . '}',
            'where'      => [
                $this->getLocaleKey() => '[a-zA-Z]{2}'
            ],
            'middleware' => LaraLocService::getAppLocaleMiddleWareAlias(),
        ], $closure);
    }

    public function enableTranslationFallback()
    {
        Route::fallback(function (Request $request) {
            $locale = $request->segment(1);

            if (empty($locale) || !$this->isLocaleAvailable($locale)) {
                $redirectUrl = $this->makeRedirectUrlForLocale(
                    $request,
                    $this->getFallBackLocale()
                );

                return redirect()->to($redirectUrl);
            }

            return abort(404);
        });

        $this->fallbackRegistered = true;
    }

    public function getFallBackLocale()
    {
        return config('app.fallback_locale');
    }

    public function enableTranslationsForGroupModels($closure)
    {
        Route::group([
            'middleware' => LaraLocService::getModelLocaleMiddleWareAlias()
        ], $closure);
    }

    public function buildRouteForLocale($lang = null)
    {
        return $this->makeRedirectUrlForLocale(request(), $lang, [$this->getModelLocaleKey()]);
    }

    public function buildRouteForModelLocale($lang)
    {
        $request = request();
        $request->query->set($this->getModelLocaleKey(), $lang);

        return $this->makeRedirectUrlForLocale($request, $this->getAppLocale());
    }

    public function isAppLang($lang)
    {
        return $this->getAppLocale() === strtolower($lang);
    }

    public function isModelLang($lang)
    {
        $modelLang = session()->get($this->getModelLocaleKey(), $this->getAppLocale());

        return $modelLang === strtolower($lang);
    }

    public function getModelLocale()
    {
        if (request()->has($this->getModelLocaleKey())) {
            return session()->get($this->getModelLocaleKey(), $this->getAppLocale());
        }

        return $this->getAppLocale();
    }

    public function getAppLocale()
    {
        return app()->getLocale();
    }

    public function setAppLocale($locale)
    {
        app()->setLocale($locale);
    }

    public function getAvailableLocale()
    {
        return config('app.available_locale', [$this->getFallBackLocale()]);
    }

    public function setModelLocale($locale)
    {
        if ($this->isLocaleAvailable($locale)) {
            session()->put($this->getModelLocaleKey(), $locale);
        }
    }

    public function isLocaleAvailable(string $locale)
    {
        return in_array(strtolower($locale), $this->getAvailableLocale());
    }

    public function makeRedirectUrlForLocale(Request $request, $locale = null, $except = [])
    {
        if (empty($locale)) {
            $locale = $this->getAppLocale();
        }

        $segments = $request->segments();

        if ($this->isLocaleAvailable($segments[0] ?? '')) {
            $segments[0] = strtolower($locale);
        } else {
            array_unshift($segments, $this->getFallBackLocale());
        }

        $queryString = http_build_query(
            collect($request->query)->except($except)->toArray()
        );

        return '/' . implode('/', $segments) . ($queryString ? '?' . $queryString : '');
    }

    public function getLocaleKey()
    {
        return 'locale';
    }

    public function getModelLocaleKey()
    {
        return 'lmlang';
    }

    public static function getAppLocaleMiddleWareAlias()
    {
        return 'set-locale';
    }

    public static function getModelLocaleMiddleWareAlias()
    {
        return 'set-model-locale';
    }

    public static function getFacadeAccessorName()
    {
        return 'laraloc';
    }
}
