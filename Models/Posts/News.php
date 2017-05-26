<?php

namespace Syltaen\Models\Posts;

class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = ["title", "editor", "excerpt", "thumbnail"];

    protected $thumbnailsFormats = [
        "url" => [],
        "tag" => [
            "archive" => "archive"
        ]
    ];

    protected $dateFormats = [
        "short"   => "d/m/Y",
        "archive" => "<\s\\t\\r\o\\n\g>d.m</\s\\t\\r\o\\n\g>Y"
    ];

}