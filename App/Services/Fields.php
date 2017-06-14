<?php

namespace Syltaen\App\Services;

class Fields
{

    /**
     * Get the value of an ACF field
     *
     * @param string $key
     * @param int|string $post_id
     * @param $default Default value if none found
     * @param $type Suggested type for the value
     * @return Field value
     */
    public static function get($key, $post_id = null, $default = "", $type = false)
    {
        // get the value
        $value = get_field($key, $post_id) ?: $default;
        // change its type if suggested
        if ($type) {
            switch ($type) {
                // basic data types
                case "int":
                    $value = (int) $value;
                    break;
                case "string":
                    $value = (string) $value;
                    break;
                case "array":
                    $value = (array) $value;
                case "object":
                    $value = (object) $value;
                    break;
                case "id":
                    $value = static::extractIds($value)[0];
                    break;
                case "ids":
                    $value = static::extractIds($value);
                    break;
                default: // use a/several specific model(s)
                    $ids     = static::extractIds($value);
                    $classes = explode(",", $type);
                    foreach ($classes as &$class) {
                        $class = "\\Syltaen\\Models\\".trim($class);
                    }
                    $model = array_shift($classes);
                    $model = new $model;
                    foreach ($classes as $class) {
                        $model->join(new $class);
                    }
                    $value = $model->is($ids)->get();
                break;
            }
        }

        return $value;
    }

    /**
     * Update the value of an ACF field
     *
     * @param stirng $key
     * @param $value
     * @param int|string $post_id
     * @return void
     */
    public static function update($key, $value, $post_id = null)
    {
        update_field($key, $value, $post_id);
    }

    /**
     * Store one or several fields values in a provided array
     *
     * @param array $array
     * @param array $keys
     * @param int|string $post_id
     * @return array $data
     */
    public static function store(&$array, $keys = null, $post_id = null)
    {
        foreach ($keys as $key=>$value) {
            // no default value
            if (is_int($key)) {
                $key = $value;
                $value = false;
            }

            // check if the value's type is suggested
            $type = false;
            if (preg_match('/\((.*)\)\s(.*)/', $key, $keys)) {
                $type = $keys[1];
                $key  = $keys[2];
            }

            // check key aliases
            $field_key = $store_key = $key;
            if (preg_match('/(.*)@(.*)/', $key, $keys)) {
                $field_key = $keys[1];
                $store_key = $keys[2];
            }

            // get value and type it as/if suggested
            $value = $field_key ? self::get($field_key, $post_id, $value, $type) : $value;

            // store value in the array or object provided
            if (is_array($array)) {
                $array[$store_key] = $value;
            } elseif (is_object($array)) {
                $array->$store_key = $value;
            }
        }

        return $array;
    }

    /**
     * Get a list of IDs from a value
     *
     * @param array|WP_Post|int $posts
     * @return void
     */
    public static function extractIds($posts)
    {
        if (is_array($posts) && isset($posts["ID"])) return [$posts["ID"]];
        if (is_object($posts) && isset($posts->ID)) return [$posts->ID];
        if (is_int($posts)) return [$posts];

        $ids = [];
        foreach ($posts as $post) {
            if (is_int($post)) {
                $ids[] = $post;
            } else {
                if (is_array($post) && isset($post["ID"])) $ids[] = $post["ID"];
                if (is_object($post) && isset($post->ID)) $ids[] = $post->ID;
            }
        }
        return $ids;
    }
}