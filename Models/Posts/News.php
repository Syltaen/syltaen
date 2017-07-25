<?php

namespace Syltaen;

class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";

    const HAS_EDITOR    = true;
    const HAS_THUMBNAIL = true;
    const HAS_EXCERPT   = true;

    protected $thumbnailsFormats = [
        "tag" => [
            "single"  => [900, null],
            "archive" => [500, null]
        ],
        "url" => [
            "slide"   => [1600, null],
            "archive" => [500, null]
        ]
    ];

    protected $dateFormats = [
        "short"   => "d/m/Y"
    ];

    protected $termsFormats = [
        "NewsTaxonomy" => [
            "names@list"    => ", ",
            "slugs@classes" => " "
        ]
    ];
}