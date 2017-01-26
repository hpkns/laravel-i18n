<?php

namespace Hpkns\I18n;

use Illuminate\Support\ServiceProvider;

class I18nServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton('locale', function ($app) {
            $available = config('app.locales', [config('app.locale')]);
            $cookie_key = config('app.local-cookie-key', 'locale');
            return new LocaleManager($available, $cookie_key);
        });

        $this->app->alias('locale', LocaleManager::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['locale'];
    }
}
