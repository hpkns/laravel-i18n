<?php namespace Kowali\I18n;

use Illuminate\Support\ServiceProvider;

use Kowali\Formatting\Parser;
use Kowali\Formatting\Parsers\MarkdownParser;

class I18nServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     *
     * @return void
     */
    public function register (){

        $this->app->bind('kowali.locale', function($app){

            $locales = $app->config->get('app.locales', (array)$app->config->get('app.locale'));
            $cookie_key = $app->config->get('app.locale-cookie-key', 'locale');

            return new LocaleManager($locales, $cookie_key, $app['request'], $app);

        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['kowali.locale'];
    }
}


