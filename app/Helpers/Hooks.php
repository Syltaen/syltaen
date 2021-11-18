<?php

namespace Syltaen;

abstract class Hooks
{
    /**
     * Register a new ajax hook
     *
     * @param string $name The hook name
     * @param callable $callback The function to run
     * @param boolean $private
     * @return void
     */
    public static function ajax($name, $callback = false, $private = false)
    {
        // No callback : execute ajax
        if (!$callback) {
            return self::do("wp_ajax_" . $name);
        }

        // Else : Register hook
        add_action("wp_ajax_".$name, $callback);
        if (!$private) {
            add_action("wp_ajax_nopriv_".$name, $callback);
        }
    }


    /**
     * Register a hook
     *
     * @param string|array $hooks
     * @param callable $callback
     * @return void
     */
    public static function add($hooks, $callback, $priority = 10, $accepted_args = 1)
    {
        foreach ((array) $hooks as $index=>$hook) {
            add_filter(
                $hook,
                $callback,
                (int) (is_array($priority) ? $priority[$index] : $priority),
                (int) (is_array($accepted_args) ? $accepted_args[$index] : $accepted_args)
            );
        }
    }

    /**
     * Call an action hook by its name
     *
     * @param string $name
     * @return void
     */
    public static function do($name)
    {
        do_action($name);
    }

    /**
     * Find a hook callback by its function name and remove it
     *
     * @return void
     */
    public static function findAndRemove($hook, $function_name, $trigger = "init")
    {
        add_action($trigger, function () use ($hook, $function_name) {
            global $wp_filter;

            foreach ($wp_filter[$hook]->callbacks as $priority=>$callbacks) {

                foreach (array_keys($callbacks) as $callback) {
                    if (strpos($callback, $function_name)) {
                        remove_filter($hook, $callback, $priority);
                    }
                }
            }
        });
    }
}