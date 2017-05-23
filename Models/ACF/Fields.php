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
        foreach ($keys as $key=>$value) {

            if (is_int($key)) {
                $key = $value;
                $value = false;
            }

            $field_key = $store_key = $key;
            if (preg_match('/(.*)@(.*)/', $key, $keys)) {
                $field_key = $keys[1];
                $store_key = $keys[2];
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