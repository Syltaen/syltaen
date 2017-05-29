<?php

namespace Syltaen\Models\Posts;

class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = ["title", "editor", "excerpt", "thumbnail"];

    protected $thumbnailsFormats = [
        "url" => [
            "archive" => [400, 400]
        ],
        "tag" => [
            "archive"   => [400, 400],
            "large"     => [1600, 400]
        ]
    ];

    protected $dateFormats = [
        "short" => "d/m/Y",
        "bold"  => "<\s\\t\\r\o\\n\g>d.m</\s\\t\\r\o\\n\g>Y"
    ];

}