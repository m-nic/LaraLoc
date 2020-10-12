<?php

use mNic\LaraLoc\LaraLocService;

static $fallbackRegistered = false;

if (!function_exists('laraloc')) {
    /**
     * @return LaraLocService
     * @throws Throwable
     */
    function laraloc()
    {
        return app()->make('laraloc');
    }
}

if (!function_exists('enableTranslationRoutes')) {
    function enableTranslationRoutes($closure)
    {
        $serviceInstance = laraloc();

        $serviceInstance->enableTranslationForGroup($closure);
        if (!$serviceInstance->hasRegisteredFallbackRoute()) {
            $serviceInstance->enableTranslationFallback();
        }
    }
}

if (!function_exists('enableTranslationModelRoutes')) {
    function enableTranslationModelRoutes($closure)
    {
        laraloc()->enableTranslationsForGroupModels($closure);
    }
}

if (!function_exists('getCurrentRouteForLocale')) {
    function getCurrentRouteForLocale($lang = null)
    {
        return laraloc()->buildRouteForLocale($lang);
    }
}

if (!function_exists('getCurrentRouteForModelLocale')) {
    function getCurrentRouteForModelLocale($lang = null)
    {
       return laraloc()->buildRouteForModelLocale($lang);
    }
}
if (!function_exists('getActiveCssClass')) {
    function getActiveCssClass($lang, $positiveClass = 'active', $negativeClass = '')
    {
        return laraloc()->isAppLang($lang) ? $positiveClass : $negativeClass;
    }
}

if (!function_exists('getActiveModelCssClass')) {
    function getActiveModelCssClass($lang, $positiveClass = 'active', $negativeClass = '')
    {
        return laraloc()->isModelLang($lang) ? $positiveClass : $negativeClass;
    }
}
