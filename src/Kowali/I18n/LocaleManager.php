<?php namespace Kowali\I18n;

use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class LocaleManager {

    /**
     * A list of availabel locales.
     *
     * @var array
     */
    protected $availableLocales = [];

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Initialize the instance.
     *
     * @param  array                              $locales
     * @param  \Illuminate\Http\Request           $request
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    public function __construct($locales, Request $request = null, Application $app = null)
    {
        $this->availableLocales = $locales;
        $this->request = $request ?: \App::make('request');
        $this->app = $app ?: \App::make('app');
    }

    /**
     * Detect the user's desired locale.
     *
     * @return string
     */
    public function guess()
    {
        // Is a locale cookies set? If so, let's try to use it
        if(($locale = $this->getCookie('locale')) && $this->isAvailable($locale))
        {
            return $locale;
        }

        // No cookie? Let's use the headers instead, then
        return $this->pickFromAccepted($this->availableLocales, $this->getHeaderAcceptedLocales());
    }

    /**
     * Extract the cookie value from the request.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getCookie($key, $default = null)
    {
        return $this->request->cookie($key, $default);
    }

    /**
     * Retunr a list of accept locale from header.
     *
     * @return array
     */
    public function getHeaderAcceptedLocales()
    {
        return explode(',', $this->request->server('HTTP_ACCEPT_LANGUAGE'));
    }

    /**
     * Return wether a locale is available
     *
     * @param  string $locale
     * @return bool
     */
    public function isAvailable($locale)
    {
        return in_array($locale, $this->availableLocales);
    }

    /**
     * Detects the user agent's prefered locale
     *
     * @param  array $available
     * @return mixed
     */
    public function pickFromAccepted(array $available, array $accept = [])
    {
        if(empty($available))
        {
            throw new Exceptions\NoAvailableLocalesSetException;
        }
        $locale = $available[0];
        $index = 0;

        foreach($accept as $hl)
        {
            $details = explode(';q=', $hl);
            // localeauges without a 'q' value have a priority of 1
            if(count($details) == 1)
                $details[1] = 1;

            if(in_array($details[0], $available) && $details[1] > $index)
            {
                $locale = $details[0];
                $index = $details[1];
            }
        }
        return $locale;
    }

    /**
     * Set the application locale
     *
     * @param  string $locale
     * @return string
     */
    public function set($locale)
    {
        if(in_array($locale, $this->availableLocales))
        {
            $this->app->setLocale($locale);
            return $locale;
        }

        return false;
    }

    /**
     * Guess the locale and set it
     *
     * @return string
     */
    public function setGuessed()
    {
        return $this->set($this->guess());
    }
}
