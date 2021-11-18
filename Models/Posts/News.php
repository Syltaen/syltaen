<?php

namespace Syltaen;

class News extends PostsModel
{
    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";

    const HAS_EDITOR    = true;
    const HAS_THUMBNAIL = true;
    const HAS_EXCERPT   = true;

    const TAXONOMIES = [
        NewsTaxonomy::SLUG
    ];

    public $thumbnailsFormats = [
        "tag" => [
            "single"  => [900, null],
            "archive" => [500, null]
        ],
        "url" => [
            "slide"   => [1600, null],
            "archive" => [500, null]
        ]
    ];

    public $dateFormats = [
        "short"   => "d/m/Y"
    ];

    public function __construct() {
        parent::__construct();

        $this->addTermsFormats([
            "(all) NewsTaxonomy@list" => function ($terms) {
                return implode(", ", array_map(function ($term) {
                    return "<a href='{$term->url}'>{$term->name}</a>";
                }, $terms));
            }
        ]);
    }
}