<?php

namespace Syltaen;

abstract class Route
{
    /**
     * Launch a controller method or a function with some arguments
     *
     * @param  [type]  $resp
     * @param  boolean $args
     * @return void
     */
    public static function respond($resp, $args = false, $same_session_page = false)
    {
        // In case of bad routing, uncomment to look what the response is
        // Controller::log($resp, "response");

        // Clear obsolete flash data
        if (!$same_session_page) {
            Data::goToNextSessionPage();
        }

        // Class method call
        if (is_string($resp)) {
            // Extracts method
            $method = false;
            if (preg_match('/(.*)::(.*)/', $resp, $keys)) {
                $resp   = $keys[1];
                $method = $keys[2];
            }

            // Add namespace to class
            $classname = "Syltaen\\$resp";

            // Instanciate the class with the arguments
            $class = new $classname($args);

            // Lauch mehtod if any
            if ($method) {
                $class->$method();
            }
        }

        // Closure function call
        if (is_callable($resp)) {
            $resp($args);
        }

        exit;
    }

    // ==================================================
    // > RULES
    // ==================================================
    /**
     * @param $resp
     * @param array   $args
     */
    public static function any($resp, $args = [])
    {
        if ($resp) {
            static::respond($resp, $args);
        }
        return true;
    }

    /**
     * Custom route defined in app/config/route.php
     *
     * @param  string $key  The key set for the route
     * @param  mix    $resp The response to use
     * @param  array  $args Arguments for the response
     * @return void
     */
    public static function custom($key, $resp, $args = [])
    {
        if (!static::qVar("custompage")) {
            return false;
        }

        if (static::qVar("custompage") != $key) {
            return false;
        }

        // Process arguments
        $clean_args = [];
        $i          = 0;
        foreach ($args as $key => $arg) {
            // Default values
            if (is_string($key) && !static::qVar("arg$i")) {
                $clean_args[$key] = $arg;

            } else {
                // Default value set, but not used.
                // So fallback to get the query var
                if (is_string($key)) {
                    $arg = $key;
                }

                // Should be now
                $clean_args[$arg] = static::qVar("arg$i");
            }

            $i++;
        }

        if ($resp) {
            static::respond($resp, $clean_args);
        }

        return true;
    }

    /**
     * Check is one or several queryStrings are defined and trigger a response
     *
     * @param  string|$query           $query_strings
     * @param  boolean|string|callable $resp
     * @param  array                   $args
     * @return void
     */
    public static function query($query_strings, $resp = false, $args = [])
    {
        $query_values = [];

        if (is_array($query_strings)) {
            foreach ($query_strings as $query_string) {
                if (!isset($_GET[$query_string])) {
                    return false;
                }

                $query_values[] = $_GET[$query_string];
            }
        } else {
            if (!isset($_GET[$query_strings])) {
                return false;
            }

            $query_values = $_GET[$query_strings];
        }

        if ($resp) {
            static::respond($resp, array_merge((array) $query_values, $args));
        }

        return $query_values;
    }

    /**
     * Test one of WordPress own rooting condition and trigger a response
     *
     * @param  string    $condition
     * @param  boolean   $resp
     * @param  array     $args
     * @return boolean
     */
    public static function is($condition, $resp = false, $args = null)
    {
        $conditions = (array) $condition;

        foreach ($conditions as $condition) {
            $argument = null;

            if (preg_match('/(.*):(.*)/', $condition, $parts)) {
                $condition = $parts[1];
                $argument  = $parts[2];
            }

            $condition = "is_" . $condition;
            if (function_exists($condition) && $condition($argument)) {
                if ($resp) {
                    static::respond($resp, $args);
                }
                return true;
            };
        }
        return false;
    }

    /**
     * Website is in maintenance mode
     *
     * @param  boolean $resp
     * @return void
     */
    public static function maintenance($resp = false)
    {
        if (Data::get("maintenance_mode", "option") && !current_user_can("administrator")) {
            static::respond($resp);
        }
    }

    // ==================================================
    // > CUSTOM ROUTES
    // ==================================================
    /**
     * Register a new query_var or get its value
     *
     * @param  string|array $keys
     * @return void
     */
    public static function qVar($keys)
    {
        // Read
        if (is_string($keys)) {
            return get_query_var($keys, false);
        }

        // Register
        if (is_array($keys)) {
            add_filter("query_vars", function ($query_vars) use ($keys) {
                return array_unique(array_merge($query_vars, $keys));
            });
        }

        return false;
    }

    /**
     * Register new custom routes
     *
     * @param  string $pattern
     * @param  string $match
     * @return void
     */
    public static function add($patterns, $match = false)
    {
        $auto_query_vars = [];

        foreach ($patterns as $key => $pattern) {
            // Auto-register query vars that are inside parenthesis
            if (
                is_array($pattern) &&
                preg_match_all('/\(([^\(\)]*)\)/', $pattern[1], $pattern_query_vars)
            ) {
                // Add query vars to the auto register
                $auto_query_vars = array_merge($auto_query_vars, $pattern_query_vars[1]);

                // remove parenthesis from the match
                $pattern[1] = str_replace(["(", ")"], "", $pattern[1]);
            }

            // Custom page url
            if (is_string($key)) {
                $match             = "index.php?custompage=$key";
                $auto_query_vars[] = "custompage";

                if (preg_match_all('/\([^\(\)]*\)/', $pattern, $args)) {
                    foreach ($args[0] as $i => $arg) {
                        $match .= "&arg$i=" . '$matches[' . ($i + 1) . ']';
                        $auto_query_vars[] = "arg$i";
                    }
                }

                $pattern = [$pattern, $match];
            }

            add_rewrite_rule($pattern[0], $pattern[1], "top");
        }

        static::qVar($auto_query_vars);
    }

    // ==================================================
    // > UTILITIES
    // ==================================================
    /**
     * Redirect to a different page
     *
     * @param  string|int $path slug, url or id of the page to redirect to. Default: homepage
     * @return void
     */
    public static function redirect($path = "", $code = 302)
    {
        if (is_string($path)) {
            if (preg_match("~^(?:f|ht)tps?://~i", $path)) {
                wp_redirect($path, $code);
            } else {
                wp_redirect(site_url($path), $code);
            }
        }

        if (is_int($path)) {
            wp_redirect(get_the_permalink($path), $code);
        }

        exit;
    }

    /**
     * Get the full current URL with all its parameters
     *
     * @param  array   $query_args
     * @param  boolean $merge
     * @return string  The URL
     */
    public static function getFullUrl($query_args = [], $merge = true)
    {
        global $wp;
        if ($merge) {
            $query_args = array_merge($_GET, $query_args);
        }

        return site_url(add_query_arg($query_args, $wp->request));
    }
}