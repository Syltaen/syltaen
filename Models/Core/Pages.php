<?php

namespace Syltaen;

class Pages extends PostsModel
{

    const TYPE     = "page";
    const LABEL    = "Pages";

    /**
     * Prevent the registering of this post type, as it is already by default
     *
     * @return false
     */
    public static function register()
    {
        return false;
    }

}