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
        return get_field($key, $post_id) ? get_field($key, $post_id) : $default;
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
     * @param array $data
     * @param array $keys
     * @param int|string $post_id
     * @return $data
     */
    public static function store(&$data, $keys = null, $post_id = null)
    {
        foreach ($keys as $key) {

            $default_value = is_array($key) ? $key[1]: false;
            $store_key     = is_array($key) ? $key[0]: $key;

            if (preg_match('/@/', $store_key, $key)) {
                $store_key = $key[0];
                $field_key = $key[1];
            } else {
                $field_key = $store_key;
            }

            $data[$store_key] = self::get($field_key, $post_id, $default_value);
        }

        return $data;
    }
}