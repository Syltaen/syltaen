<?php

namespace Syltaen\Models\ACF;

class Sections extends Fields
{

    /**
     * Get the field and all its sub fields
     *
     * @param string $key
     * @param int|string $post_id
     * @return array : the whole field value
     */
    public static function get($key = "sections", $post_id = null)
    {
        $sections = get_filed($key);

        foreach ($sections as $s) {

            $c = $s["content"];
            switch ($c["layout"]) {


                // ==================================================
                // > TEXT ONE COLUM
                // ==================================================
                case "":;

                // ==================================================
                // > TEXT TWO COLUM
                // ==================================================
                case "":;

                // ==================================================
                // > ARCHIVE
                // ==================================================
                case "":;

                default: break;
            }
        }

        return $sections;
    }

    /**
     * Store the section value in a provided array
     *
     * @param array $data
     * @param string $key
     * @param int|string $post_id
     * @return $data
     */
    public static function store($data, $keys = "sections", $post_id = null)
    {
        parent::store($data, $keys, $post_id);
    }
}