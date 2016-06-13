<?php

if (!function_exists('alternate')) {
    /**
     * Return the current route but without a different parameter.
     *
     * @param array $replace
     *
     * @return string
     */
    function alternate(array $replace = [])
    {
        $route = Route::currentRouteName();
        $query = array_merge(app('request')->query(), $replace);

        return route($route, $query);
    }
}
