<?php

namespace Hpkns\I18n\Facades;

use Illuminate\Support\Facades\Facade;

class Locale extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'locale';
    }
}
