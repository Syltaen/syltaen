<?php

namespace Syltaen\App\Services;

class Routes
{

    /**
     * Register a new query_var
     *
     * @param string|array $keys
     * @return void
     */
    public static function registerVar($keys)
    {
        add_filter("query_vars", function ($query_vars) {
            foreach ($keys as $key) {
                $query_vars[] = $key;
            }
            return $query_vars;
        });
    }

    /**
     * Get the value of a query_var
     *
     * @param string $key
     * @return void
     */
    public static function getVar($key)
    {
        return get_query_var($key, false);
    }

    /**
     * Register a new custom route
     *
     * @param string $pattern
     * @param string $match
     * @param string $priority
     * @return void
     */
    public static function register($pattern, $match, $priority = "top")
    {
        add_rewrite_rule($pattern, $match, $priority);
    }
}