<?php

namespace mNic\LaraLoc;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use mNic\LaraLoc\Http\Middleware\SetLocale;
use mNic\LaraLoc\Http\Middleware\SetModelLocale;

class LaraLocServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->registerHelpers();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', LaraLocService::getFacadeAccessorName());

        $router->aliasMiddleware(LaraLocService::getAppLocaleMiddleWareAlias(), SetLocale::class);
        $router->aliasMiddleware(LaraLocService::getModelLocaleMiddleWareAlias(), SetModelLocale::class);

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LaraLocService::getFacadeAccessorName(), function () {
            return new LaraLocService();
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [LaraLocService::getFacadeAccessorName()];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/2019_10_09_071145_model_translations.php' => database_path('migrations/2019_10_09_071145_model_translations.php'),
        ], LaraLocService::getFacadeAccessorName() . '.migration');

        // Publishing the views.
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/mnic'),
        ], LaraLocService::getFacadeAccessorName() . '.views');
    }

    private function registerHelpers()
    {
        if (file_exists($file = __DIR__ . '/Helpers/helpers.php')) {
            require $file;
        }
    }
}
