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
     * The name of the cookie key for storing the locale.
     *
     * @var string
     */
    protected $cookieKey;

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
    public function __construct($locales, $cookie_key = 'locale', Request $request = null, Application $app = null)
    {
        $this->availableLocales = $locales;
        $this->cookieKey = $cookie_key;
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
        //dd( \Cookie::get('locale'));
        if(($locale = $this->getCookie($this->cookieKey)) && $this->isAvailable($locale))
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
     * Return a list of available locales.
     *
     * @return array
     */
    public function available()
    {
        return $this->availableLocales;
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
    public function set($locale, $save = false)
    {
        if(in_array($locale, $this->availableLocales))
        {
            $this->app->setLocale($locale);

            if($save)
            {
                $this->saveLocale($locale);
            }

            return $locale;
        }

        return false;
    }

    /**
     * Save the locale
     *
     * @return void
     */
    public function saveLocale($locale)
    {
        if($locale != $this->getCookie($this->cookieKey))
        {
            \Log::info('Saving cookie');
            \Cookie::queue($this->cookieKey, $locale, 144000);
        }
    }

    /**
     * Return the current locale.
     *
     * @return string
     */
    public function get()
    {
        return $this->app->getLocale();
    }

    /**
     * Guess the locale and set it
     *
     * @return string
     */
    public function setGuessed($save = false)
    {
        return $this->set($this->guess(), $save);
    }

    /**
     * Return the cookie key.
     *
     * @return string
     */
    public function getCookieKey()
    {
        return $this->cookieKey;
    }
}
