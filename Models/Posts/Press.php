<?php

namespace Syltaen\Models\Posts;

class Press extends Posts
{

    const TYPE     = "press";
    const LABEL    = "Press";
    const ICON     = "dashicons-clipboard";
    const HAS_PAGE = false;
    const SUPPORTS = ["title", "excerpt", "thumbnail"];

    const CUSTOM_PAGE_FIELD = "external_link";

    protected $thumbnailsFormats = [
        "url" => [
            "archive" => [400, 400]
        ],
        "tag" => [
            "archive"   => [400, 400]
        ]
    ];

    protected $dateFormats = [
        "bold"  => "<\s\\t\\r\o\\n\g>d.m.</\s\\t\\r\o\\n\g>Y"
    ];
}