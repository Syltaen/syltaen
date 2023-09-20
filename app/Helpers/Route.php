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
     * @param  string|array $routename The key set for the route
     * @param  mix          $resp      The response to use
     * @param  array        $args      Arguments for the response
     * @return void
     */
    public static function custom($routename, $resp, $args = [])
    {
        if (!static::qVar("custompage")) {
            return false;
        }

        if (!in_array(static::qVar("custompage"), (array) $routename)) {
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
        global $syltaen_custom_routes;
        $auto_query_vars       = [];
        $syltaen_custom_routes = $syltaen_custom_routes ?? [];

        foreach ($patterns as $key => $pattern) {
            // Custom page url : normalize format
            if (is_string($key)) {
                $query   = "index.php?(custompage)={$key}";
                $pattern = array_values((array) $pattern);

                if (preg_match_all('/\([^\(\)]*\)/', $pattern[0], $args)) {
                    foreach ($args[0] as $i => $arg) {
                        $query .= "&(arg$i)=" . '$matches[' . ($i + 1) . ']';
                    }
                }

                $syltaen_custom_routes[$key] = $pattern;
                $pattern                     = [$pattern, $query];
            }

            // Matches should always be an array
            $pattern[0] = static::autoTranslateRoute($pattern[0]);

            // Auto-register query vars that are inside parenthesis
            if (preg_match_all('/\(([^\(\)]*)\)/', $pattern[1], $pattern_query_vars)) {
                // Add query vars to the auto register
                $auto_query_vars = array_merge($auto_query_vars, $pattern_query_vars[1]);
                // remove parenthesis from the match
                $pattern[1] = str_replace(["(", ")"], "", $pattern[1]);
            }

            foreach ($pattern[0] as $match) {
                add_rewrite_rule($match, $pattern[1], "top");
            }
        }

        static::qVar($auto_query_vars);
    }

    /**
     * Return URL regex matching for CRUD actions
     *
     * @param  string  $post_type
     * @param  string  $archive
     * @return array
     */
    public static function crud($post_type, $archive = false)
    {
        $archive = $archive ?: $post_type;

        return [
            $archive . '/([^\/]+)/([0-9a-z-]+)?/?$',
            'index.php?name=$matches[1]&post_type=' . $post_type . '&(route)=$matches[2]',
        ];
    }

    /**
     * Return URL regex matching for CRUD actions
     *
     * @return array
     */
    public static function userCrud($archive)
    {
        return [
            [
                $archive . '([^\/]+)/?$',
                'index.php?author_name=$matches[1]&(route)=display',
            ],
            [
                $archive . '([^\/]+)/([0-9a-z-]+)?/?$',
                'index.php?author_name=$matches[1]&(route)=$matches[2]',
            ],
        ];
    }

    /**
     * Get a custom route's URL
     *
     * @return string
     */
    public static function getCustom($key)
    {
        global $syltaen_custom_routes;

        $lang_index = array_search(Lang::getCurrent(), Lang::getList());

        $path = $syltaen_custom_routes[$key][$lang_index] ?? ($syltaen_custom_routes[$key][0] ?? false);

        return site_url(trim($path, "$?^"));
    }

    /**
     * Add lang prefixes for routes that have not been translated
     *
     * @param  array   $route
     * @return array
     */
    public static function autoTranslateRoute($route)
    {
        $langs = Lang::getList();
        $route = (array) $route;

        // All translations provided
        if (count($langs) == count($route)) {return $route;}

        foreach ($langs as $i => $lang) {
            if (!empty($route[$i])) {continue;}
            $route[$i] = "$lang/{$route[0]}";
        }

        return $route;
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
