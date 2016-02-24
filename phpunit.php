<?php

require __DIR__ . '/vendor/autoload.php';

class App
{
    static $instance = null;

    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    public static function getInstance()
    {
        return static::$instance;
    }
}

function app(){
    return App::getInstance();
}
