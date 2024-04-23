<?php

namespace Syltaen;

abstract class Data
{
    /**
     * Get the value of an ACF field
     *
     * @param  string     $key
     * @param  int|string $post_id
     * @param  $default   Default    value if none found
     * @param  $filter    Auto       filter the value
     * @return mixed      A field value
     */
    public static function get($key, $post_id = null, $default = "", $filter = false)
    {
        // get the value
        $value = \get_field($key, $post_id);

        // filter the value if suggested
        if ($filter && $value) {
            $value = static::filter($value, $filter);
        }

        return $value ?: $default;
    }

    /**
     * Transform the value to get something specific
     *
     * @param  mixed  $value  The value to filter
     * @param  string $filter The filter to use
     * @return mixed  The filtered value
     */
    public static function filter($value, $filter)
    {
        switch ($filter) {
            case "int":
                return (int) $value;
            case "string":
                return (string) $value;
            case "array":
                return (array) $value;
            case "object":
                return (object) $value;
            case "id":
                return static::extractIds($value)[0];
            case "ids":
                return static::extractIds($value);
            case "address":
                return Text::address($value);
            case "img:tag":
            case "img":
                return wp_get_attachment_image(static::extractIds($value)[0], "full");
            case "img:url":
                return wp_get_attachment_url(static::extractIds($value)[0], "full");
            case "img:svg":
                return file_get_contents(static::filter($value, "img:url"));
            case "url":
                if (!preg_match("~^(?:f|ht)tps?://~i", $value)) {
                    return "http://" . $value;
                }
                return $value;
            case "json_decode":
                if (is_object($value) || is_array($value)) {
                    return $value;
                }
                return json_decode(stripslashes($value));
            default: // Classes
                $filter = explode("::", $filter);
                $class  = "\\Syltaen\\" . ($filter[0] ?? "Posts");
                $method = $filter[1] ?? (is_subclass_of($class, Model::class) ? "is" : false);

                // No method : instanciate class
                if (!$method) {
                    return new $class($value);
                }

                // Method : use statically or on new instance
                if ((new \ReflectionMethod($class, $method))->isStatic()) {
                    return $class::$method($value);
                } else {
                    return (new $class())->$method($value);
                }
        }
    }

    /**
     * Apply a filter to all string found in an array, recursively
     *
     * @param  array  $array
     * @return void
     */
    public static function recursiveFilter($array, $filter)
    {
        array_walk_recursive($array, function (&$value, $key) use ($filter) {
            if (is_string($value)) {
                $value = Data::filter($value, $filter);
            }

            if (is_object($value)) {
                // Do not filter models
                if ($value instanceof Model) {
                    return;
                }

                if ($value instanceof Set) {
                    error_log($key . " - " . get_class($value));
                    $value = (array) $value;
                };

                foreach ($value as $key => &$attr) {
                    if (is_object($attr)) {
                        error_log("-" . $key . " - " . get_class($attr));
                    }

                    if (is_string($attr)) {
                        $attr = Data::filter($attr, $filter);
                    }
                }
            }
        });

        return $array;
    }

    /**
     * Update the value of an ACF field
     *
     * @param  stirng     $key
     * @param  $value
     * @param  int|string $post_id
     * @param  bool       $merge     Only update the field if no value exists
     * @return void
     */
    public static function update($key, $value, $post_id = null, $merge = false)
    {
        if (!$merge || !Data::get($key, $post_id)) {
            update_field($key, $value, $post_id);
        }
    }

    /**
     * Take a field key and parse its value
     *
     * @return array
     */
    public static function getAdvanced($key, $value, $post_id = null, $callback_context = false)
    {
        // Get the key parts (store, meta, filter)
        $key_parts = Data::parseDataKey($key);

        // get value and filter it as/if suggested
        $value = $key_parts["meta"] ? self::get($key_parts["meta"], $post_id, $value, $key_parts["filter"]) : $value;

        // Execute callable functions
        if (is_callable($value) && !is_string($value)) {
            $value = $value($callback_context);
        }

        return [
            "key"   => $key_parts["store"],
            "value" => $value,
        ];
    }

    /**
     * Normalize the keys of a list of fields
     *
     * @param  Set|array $fields
     * @return Set
     */
    public static function normalizeFieldsKeys($fields)
    {
        return (set($fields))->mapAssoc(function ($key, $value) {
            if (is_int($key)) {
                return [$value => null];
            }

            return [$key => $value];
        });
    }

    /**
     * Create an index for the given fields, as property=>field key
     *
     * @param  Set   $fields Set of fields
     * @return Set
     */
    public static function generateFieldsIndex($fields)
    {
        return $fields->mapAssoc(function ($key, $value) {
            $parts = static::parseDataKey($key);
            return [$parts["store"] => $key];
        });
    }

    /**
     * Get the different parts of a data key : store, meta, filter
     *
     * @param  string
     * @return array
     */
    public static function parseDataKey($key)
    {
        // Check for a filter in ()
        $filter = false;
        if (preg_match('/\((.*)\)\s(.*)/', $key, $keys)) {
            $filter = $keys[1];
            $key    = $keys[2];
        }

        // Check for aliases (different storage and meta key)
        $meta = $store = $key;
        if (preg_match('/(.*)@(.*)/', $key, $keys)) {
            $meta  = $keys[1];
            $store = $keys[2];
        }

        return [
            "filter" => $filter,
            "meta"   => $meta,
            "store"  => $store,
        ];
    }

    /**
     * Get a list of IDs from a value
     *
     * @param  array|WP_Post|int $data
     * @return array
     */
    public static function extractIds($data)
    {
        if (is_array($data) && isset($data["ID"])) {
            return [$data["ID"]];
        }

        if (is_object($data) && isset($data->ID)) {
            return [$data->ID];
        }

        if (is_array($data) && isset($data["id"])) {
            return [$data["id"]];
        }

        if (is_object($data) && isset($data->id)) {
            return [$data->id];
        }

        if (is_int($data)) {
            return [$data];
        }

        if (is_string($data)) {
            return [intval($data)];
        }

        if ($data instanceof Model) {
            return (array) $data->ID;
        }

        if (is_array($data)) {
            return array_map(function ($data) {
                return static::extractIds($data)[0];
            }, $data);
        }

        return false;
    }

    /**
     * Get an option for the ACF page
     *
     * @param  string  $key
     * @return mixed
     */
    public static function option($key, $fallback = false, $filter = false)
    {
        return Data::get($key, "options", $fallback, $filter);
    }

    // ==================================================
    // > SESSIONS
    // ==================================================
    /**
     * Get a value from the session or store one
     *
     * @param  array|string $data If array, store. If string, read.
     * @return void
     */
    public static function session($data = null, $session_key = "syltaen")
    {
        if (!session_id()) {
            session_start();
        }

        // write
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $_SESSION[$session_key][$key] = $value;
            }
            return true;
        }

        // read one
        if (is_string($data)) {
            if (isset($_SESSION[$session_key][$data])) {
                return $_SESSION[$session_key][$data];
            }
            return null;
        }

        // read all
        if ($session_key && isset($_SESSION[$session_key])) {
            return $_SESSION[$session_key];
        }

        return $_SESSION;
    }

    // ==================================================
    // > JAVASCRIPT
    // ==================================================
    /**
     * Register variables in the global JS scrope, from the back-end
     *
     * @param  array  $variables variable_name => variable_value
     * @return void
     */
    public static function registerJSVars($vars)
    {
        $lines = [];

        foreach ($vars as $var => $val) {
            $lines[] = "$var = " . json_encode($val) . ";";
        }

        Files::addInlineScript(
            implode("\n", $lines),
            "before",
            "bundle.js"
        );
    }

    // ==================================================
    // > FLASH MESSAGES
    // ==================================================
    /**
     * Get or store a value in the current page session
     *
     * @param  [type] $data
     * @return void
     */
    public static function currentPage($data = null)
    {
        return static::session($data, "syltaen_current_page");
    }

    /**
     * Get or store a value in the next page session
     *
     * @param  [type] $data
     * @return void
     */
    public static function nextPage($data = null, $redirection = false, $ttl = 1)
    {
        static::addFlashMessage($data, $ttl);

        if ($redirection) {
            Route::redirect($redirection);
        }
    }

    /**
     * Go to the next session page, clearing flash data
     *
     * @return void
     */
    public static function goToNextSessionPage()
    {
        if (!session_id()) {
            session_start();
        }

        $_SESSION["syltaen_current_page"] = [];

        // Remove one to all TTL
        $messages = static::getFlashMessages();

        foreach ($messages as &$message) {
            $message["ttl"]--;

            if ($message["ttl"] === 0) {
                $_SESSION["syltaen_current_page"] = $message["messages"];
            }
        }unset($message);

        // Remove expired messages
        $messages = array_filter($messages, function ($message) {
            return $message["ttl"] > 0;
        });

        $_SESSION["syltaen_messages"] = $messages;
    }

    /**
     * @param $messages
     * @param $ttl
     */
    public static function addFlashMessage($messages, $ttl = 1)
    {
        if (!session_id()) {
            session_start();
        }

        $_SESSION["syltaen_messages"] = static::getFlashMessages();

        $_SESSION["syltaen_messages"][] = [
            "messages" => $messages,
            "ttl"      => $ttl,
        ];
    }

    /**
     * @return mixed
     */
    public static function getFlashMessages()
    {
        if (empty($_SESSION["syltaen_messages"])) {
            return [];
        }

        return $_SESSION["syltaen_messages"];
    }

    // ==================================================
    // > GLOBAL DATA
    // ==================================================
    /**
     * Set globals data shared by the whole application
     *
     * @param  array|string $data
     * @param  bool         $merge
     * @return void
     */
    public static function globals($data = null, $merge = false)
    {
        global $syltaen_global_data;

        // write
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $old_value                 = isset($syltaen_global_data[$key]) ? $syltaen_global_data[$key] : [];
                    $syltaen_global_data[$key] = array_merge_recursive($old_value, $value);
                } else {
                    $syltaen_global_data[$key] = $value;
                }
            }
            return true;
        }

        // read one
        if (is_string($data)) {
            if (isset($syltaen_global_data[$data])) {
                return $syltaen_global_data[$data];
            }
            return null;
        }

        return $syltaen_global_data;
    }
}