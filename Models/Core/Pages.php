<?php

namespace Syltaen;

class Pages extends PostsModel
{

    const TYPE     = "page";
    const LABEL    = "Pages";


    /**
     * Get a page
     *
     * @return void
     */
    public static function fromPath($path)
    {
        wp_send_json(
            get_page_by_path($path)
        );
    }

}