<?php

namespace Syltaen\Models;

class Sections
{

    private $data;

    /**
     * Generate the section data
     *
     * @param string $key
     * @param int $post_id
     */
    public function __construct($key = "sections", $post_id = null)
    {
        $sections = get_field($key);

        $this->data = "tets";
    }

    public function lol()
    {
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
    }

    public function data()
    {
        return $this->data;
    }

    public function view()
    {

    }
}