<?php

namespace Syltaen\Models\Posts;

class Jobs extends Posts
{

    const TYPE     = "jobs";
    const LABEL    = "Jobs";
    const ICON     = "dashicons-businessman";
    const SUPPORTS = ["title", "editor", "excerpt"];
    const TAX      = ["countries"];

    protected $dateFormats = [
        "bold"  => "<\s\\t\\r\o\\n\g>d.m</\s\\t\\r\o\\n\g>Y"
    ];

    protected $termsFormats = [
        "countries" => [
            "names" => ", "
        ]
    ];

}