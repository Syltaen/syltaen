<?php

namespace Syltaen\Models;


class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = ["title", "editor", "excerpt", "thumbnail"];

    protected $fields = [];

    protected $thumbnails_formats = [];

    protected $date_formats = [
        "short" => "d/m/Y"
    ];

    public function __construct()
    {
        parent::__construct();
        // $this->fields = array_merge($this->fields, [

        // ]);
    }
}