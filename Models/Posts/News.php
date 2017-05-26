<?php

namespace Syltaen\Models\Posts;

class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = ["title", "editor", "excerpt", "thumbnail"];

    protected $dateFormats = [
        "short" => "d/m/Y"
    ];
}