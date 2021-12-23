<?php

namespace Syltaen;

class News extends PostsModel
{
    const TYPE  = "news";
    const LABEL = "News";
    const ICON  = "dashicons-megaphone";

    const HAS_EDITOR    = true;
    const HAS_THUMBNAIL = true;
    const HAS_EXCERPT   = true;

    const TAXONOMIES = [
        NewsTaxonomy::class,
    ];

    /**
     * @var array
     */
    public $dateFormats = [
        "short" => "d/m/Y",
    ];
}