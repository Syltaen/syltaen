<?php

namespace Syltaen\Models\ACF;

class Fields
{

    /**
     * Get the value of an ACF field
     *
     * @param string $key
     * @param int|string $post_id
     * @param $default
     * @return Field value
     */
    public static function get($key, $post_id = null, $default = "")
    {
        return get_field($key, $post_id) ?: $default;
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
     * @return $data
     */
    public static function store(&$array, $keys = null, $post_id = null)
    {
        foreach ($keys as $key) {

            $value     = is_array($key) ? $key[1]: null;
            $field_key = is_array($key) ? $key[0]: $key;

            if (preg_match('/(.*)@(.*)/', $field_key, $key)) {
                $field_key = $key[1];
                $store_key = $key[2];
            } else {
                $store_key = $field_key;
            }

            $value = $field_key ? self::get($field_key, $post_id, $value) : $value;

            if (is_array($array)) {
                $array[$store_key] = $value;
            } elseif (is_object($array)) {
                $array->$store_key = $value;
            }
        }

        return $array;
    }
}