<?php

namespace Syltaen\Models;

class News extends Post
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = array("title", "editor", "excerpt", "thumbnail");

    /**
     * addFileds
     *
     * @param array $news
     * @return array $news
     */
    static protected function addFields($news)
    {
        // return Fields::add($news, [
        // ], $news->id);
    }
}