<?php

namespace Syltaen;

abstract class Data
{

    /**
     * Get the value of an ACF field
     *
     * @param string $key
     * @param int|string $post_id
     * @param $default Default value if none found
     * @param $filter Auto filter the value
     * @return mixed A field value
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
     * @param mixed $value The value to filter
     * @param string $filter The filter to use
     * @return mixed The filtered value
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
            case "img:tag":
            case "img":
                return wp_get_attachment_image(static::extractIds($value)[0], "full");
            case "img:url":
                return wp_get_attachment_url(static::extractIds($value)[0], "full");
            case "img:svg":
                return file_get_contents(static::filter($value, "img:url"));
            case "url":
                if (!preg_match("~^(?:f|ht)tps?://~i", $value)) return "http://" . $value;
                return $value;
            case "json_decode":
                if (is_object($value) || is_array($value)) return $value;
                return json_decode(stripslashes($value));
            case "content":
                return do_shortcode($value);
            default: // use a/several specific model(s)
                $ids     = static::extractIds($value);

                $classes = explode(",", $filter);
                foreach ($classes as &$class) $class = "\Syltaen\\" . trim($class);

                $model = array_shift($classes); // Use the first model as the base model
                $model = new $model;
                foreach ($classes as $class) $model->join(new $class);

                return $model->is($ids);
            break;
        }
    }

    /**
     * Apply a filter to all string found in an array, recursively
     *
     * @param array $array
     * @return void
     */
    public static function recursiveFilter($array, $filter)
    {

        array_walk_recursive($array, function (&$value, $key) use ($filter) {
            if (is_string($value)) {
                $value = Data::filter($value, $filter);
            }

            if (is_object($value)) {
                if (!$value instanceof Model) {
                    foreach ($value as &$attr) {
                        if (is_string($attr)) {
                            $attr = Data::filter($attr, $filter);
                        }
                    }
                }
            }
        });

        return $array;
    }

    /**
     * Update the value of an ACF field
     *
     * @param stirng $key
     * @param $value
     * @param int|string $post_id
     * @param bool $merge Only update the field if no value exists
     * @return void
     */
    public static function update($key, $value, $post_id = null, $merge = false)
    {
        if (!$merge || !Data::get($key, $post_id)) {
            update_field($key, $value, $post_id);
        }
    }

    /**
     * Store one or several fields values in a provided array
     *
     * @param array $array
     * @param array $keys
     * @param int|string $post_id
     * @return array $data
     */
    public static function store(&$array, $keys, $post_id = null)
    {
        if (empty($keys)) return false;

        if (!isset($array)) $array = [];

        foreach ($keys as $key=>$value) {
            // no default value
            if (is_int($key)) {
                $key = $value;
                $value = false;

                // Anonym function
                if (is_callable($key)) {
                    $key($array);
                    continue;
                }
            }

            // check if the value's type is suggested
            $filter = false;
            if (preg_match('/\((.*)\)\s(.*)/', $key, $keys)) {
                $filter = $keys[1];
                $key  = $keys[2];
            }

            // check key aliases
            $field_key = $store_key = $key;
            if (preg_match('/(.*)@(.*)/', $key, $keys)) {
                $field_key = $keys[1];
                $store_key = $keys[2];
            }

            // get value and filter it as/if suggested
            $value = $field_key ? self::get($field_key, $post_id, $value, $filter) : $value;

            // Execute callable functions
            if (is_callable($value) && !is_string($value)) {
                $value = $value($array);
            }

            // store value in the array or object provided
            if (is_array($array)) {
                $array[$store_key] = $value;
            }

            if (is_object($array)) {
                $array->$store_key = $value;
            }
        }

        return $array;
    }

    /**
     * Get a list of IDs from a value
     *
     * @param array|WP_Post|int $data
     * @return void
     */
    public static function extractIds($data)
    {
        if (is_array($data) && isset($data["ID"])) return [$data["ID"]];
        if (is_object($data) && isset($data->ID)) return [$data->ID];
        if (is_array($data) && isset($data["id"])) return [$data["id"]];
        if (is_object($data) && isset($data->id)) return [$data->id];
        if (is_int($data)) return [$data];
        if (is_string($data)) return [intval($data)];
        if ($data instanceof Model) return (array) $data->ID;

        if (is_array($data)) return array_map(function ($data) {
            return static::extractIds($data)[0];
        }, $data);

        return false;
    }

    // ==================================================
    // > SESSIONS
    // ==================================================
    /**
     * Get a value from the session or store one
     *
     * @param array|string $data If array, store. If string, read.
     * @return void
     */
    public static function session($data = null, $session_key = "syltaen")
    {
        if (!session_id()) session_start();

        // write
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
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
     * @param array $variables variable_name => variable_value
     * @return void
     */
    public static function registerJSVars($vars)
    {
        $lines = [];

        foreach ($vars as $var=>$val) {
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
     * @param [type] $data
     * @return void
     */
    public static function currentPage($data = null)
    {
        return static::session($data, "syltaen_current_page");
    }

    /**
     * Get or store a value in the next page session
     *
     * @param [type] $data
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
        if (!session_id()) session_start();

        $_SESSION["syltaen_current_page"] = [];

        // Remove one to all TTL
        $messages = static::getFlashMessages();

        foreach ($messages as &$message) {
            $message["ttl"]--;

            if ($message["ttl"] === 0) {
                $_SESSION["syltaen_current_page"] = $message["messages"];
            }
        } unset($message);

        // Remove expired messages
        $messages = array_filter($messages, function ($message) {
            return $message["ttl"] > 0;
        });

        $_SESSION["syltaen_messages"] = $messages;
    }

    public static function addFlashMessage($messages, $ttl = 1)
    {
        if (!session_id()) session_start();

        $_SESSION["syltaen_messages"] = static::getFlashMessages();

        $_SESSION["syltaen_messages"][] = [
            "messages" => $messages,
            "ttl"      => $ttl
        ];
    }

    public static function getFlashMessages()
    {
        if (empty($_SESSION["syltaen_messages"])) return [];
        return $_SESSION["syltaen_messages"];
    }


    // ==================================================
    // > GLOBAL DATA
    // ==================================================
    /**
     * Set globals data shared by the whole application
     *
     * @param array|string $data
     * @param bool $merge
     * @return void
     */
    public static function globals($data = null, $merge = false)
    {
        global $syltaen_global_data;

        // write
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
                if (is_array($value)) {
                    $old_value = isset($syltaen_global_data[$key]) ? $syltaen_global_data[$key] : [];
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


    // ==================================================
    // > ARRAYS
    // ==================================================
    /**
     * Shortcut to keep only certain keys of an array
     *
     * @param array $array
     * @param array $keys_to_keep
     * @return array
     */
    public static function keepKeys($array, $keys_to_keep)
    {
        return array_filter($array, function ($key) use ($keys_to_keep) {
            return in_array($key, $keys_to_keep);
        }, ARRAY_FILTER_USE_KEY);
    }
}