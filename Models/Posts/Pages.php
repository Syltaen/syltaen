<?php

namespace Syltaen;

class Pages extends Posts
{

    const TYPE     = "page";
    const LABEL    = "Pages";

    /**
     * Prevent the registering of this post type
     *
     * @return false
     */
    public static function register()
    {
        return false;
    }

}