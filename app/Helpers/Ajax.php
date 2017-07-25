<?php

namespace Syltaen;

abstract class Ajax
{
    /**
     * Register a new ajax hook
     *
     * @param string $name The hook name
     * @param callable $callback The function to run
     * @param boolean $private
     * @return void
     */
    public static function register($name, $callback, $private = false)
    {
        add_action("wp_ajax_".$name, $callback);
        if (!$private) {
            add_action("wp_ajax_nopriv_".$name, $callback);
        }
    }

    /**
     * Call an ajax hook by its name
     *
     * @param string $name
     * @return void
     */
    public static function call($name)
    {
        do_action("wp_ajax_".$name);
    }
}