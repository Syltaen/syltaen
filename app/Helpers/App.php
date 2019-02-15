<?php

namespace Syltaen;

abstract class App
{
    private static $config = false;

    /**
     * Get a config item
     *
     * @return void
     */
    public static function config($key)
    {
        // Load config file once
        if (!static::$config) {
            static::$config = include Files::path("app/config/_config.php");
        }

        return static::$config[$key];
    }
}