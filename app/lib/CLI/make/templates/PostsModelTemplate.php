<?php

namespace Syltaen;

class ClassName extends PostsModel
{
    const TYPE     = "postsmodeltemplate";
    const LABEL    = "ClassName";
    const ICON     = "dashicons-megaphone";

    // const HAS_EDITOR    = true;
    // const HAS_THUMBNAIL = true;
    // const HAS_EXCERPT   = true;

    // const TAXONOMIES = [
    //     PostsModelTemplateTaxonomy::SLUG
    // ];

    // const CUSTOM_STATUS = [
    //     "old_postsmodeltemplate"  => ["PostsModelTemplate dépassé", "PostsModelTemplate dépassés"]
    // ];

    // protected $thumbnailsFormats = [
    //     "tag" => [
    //         "single"  => [900, null],
    //         "archive" => [500, null]
    //     ],
    //     "url" => [
    //         "slide"   => [1600, null],
    //         "archive" => [500, null]
    //     ]
    // ];

    // protected $dateFormats = [
    //     "short"   => "d/m/Y"
    // ];

    // protected $termsFormats = [
    //     "PostsModelTemplateTaxonomy" => [
    //         "names@list"    => ", ",
    //         "slugs@classes" => " "
    //     ]
    // ];

    public function __construct() {
        parent::__construct();

        $this->fields = [

        ];
    }
}