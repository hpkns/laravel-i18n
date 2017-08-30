<?php

namespace Hpkns\I18n;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class LocaleManager
{

    /**
     * A list of available locales.
     *
     * @var array
     */
    protected $available;

    /**
     * The name of the cookie key for storing the locale.
     *
     * @var string
     */
    protected $cookieKey;

    /**
     * A reference to the request binding
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Initialize the instance.
     *
     * @param array  $locales
     * @param string $cookie_key
     */
    public function __construct(array $available = [], $cookie_key = 'locale', $request = null)
    {
        $this->available = $available;
        $this->cookieKey = $cookie_key;
        $this->request   = $request ?: app('request');
    }

    /**
     * Detect the user's desired locale.
     *
     * @param  bool $must_return_value
     * @return string|null
     */
    public function guess($must_return_value = false)
    {
        $locale = $this->request->cookie($this->cookieKey);

        if ($locale && $this->isAvailable($locale)) {
            return $locale;
        }

        try {
            return $this->pickFromAccepted($this->available, $this->getHeaderAcceptedLocales(), $must_return_value);
        } catch (Exceptions\EmptyAcceptHeader $e) {
            return reset($this->available);
        }
    }

    /**
     * Return a list of accept locale from the HTTP.
     *
     * @throws \Hpkns\I18n\Exceptions\EmptyAcceptHeader
     * @return array
     */
    public function getHeaderAcceptedLocales()
    {
        $accepted = $this->request->server('HTTP_ACCEPT_LANGUAGE');

        if (empty($accepted)) {
            throw new Exceptions\EmptyAcceptHeader('HTTP Accept header empty or not found');
        }

        return explode(',', $accepted);
    }

    /**
     * Return wether a locale is available.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isAvailable($locale)
    {
        return in_array($locale, $this->available);
    }

    /**
     * Return a list of available locales.
     *
     * @return array
     */
    public function available()
    {
        return $this->available;
    }

    /**
     * Detects the user agent's prefered locale.
     *
     * @param  array $available
     * @param  array $accept
     * @param  bool  $must_return_value
     * @return string|null
     */
    public function pickFromAccepted(array $available, array $accept = [], $must_return_value = false)
    {
        if (empty($available)) {
            throw new Exceptions\NoAvailableLocalesSetException();
        }

        $locale = null;
        $index = 0;

        foreach ($accept as $hl) {
            $details = explode(';q=', $hl);
            // localeauges without a 'q' value have a priority of 1
            if (count($details) == 1) {
                $details[1] = 1;
            }

            if (in_array($details[0], $available) && $details[1] > $index) {
                $locale = $details[0];
                $index = $details[1];
            }
        }

        return ($locale == null && $must_return_value) ? $available[0] : $locale;
    }

    /**
     * Set the application locale.
     *
     * @param  string $locale
     * @param  bool   $save
     * @return string
     */
    public function set($locale, $save = false)
    {
        if (! $this->isAvailable($locale)) {
            return false;
        }

        app()->setLocale($locale);

        if ($save) {
            $this->saveLocale($locale);
        }

        return $locale;
    }

    /**
     * Save the locale.
     *
     * @param  string $locale
     * @return void
     */
    public function saveLocale($locale)
    {
        if (! $this->isAvailable($locale)) {
            return false;
        }

        app('cookie')->queue($this->cookieKey, $locale, 144000);

        return $locale;
    }

    /**
     * Return the current locale.
     *
     * @return string
     */
    public function get()
    {
        return app()->getLocale();
    }

    /**
     * Guess the locale and set it.
     *
     * @param  bool $save
     * @return string
     */
    public function setGuessed($save = false)
    {
        return $this->set($this->guess(true), $save);
    }
}
