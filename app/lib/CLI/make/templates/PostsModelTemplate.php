<?php

namespace Syltaen;

class ClassName extends PostsModel
{
    const TYPE  = "postsmodeltemplate";
    const LABEL = "ClassName";
    const ICON  = "dashicons-megaphone";

    // const HAS_EDITOR    = true;
    // const HAS_THUMBNAIL = true;
    // const HAS_EXCERPT   = true;

    // const TAXONOMIES = [
    //     PostsModelTemplateTaxonomy::class
    // ];

    // const CUSTOM_STATUS = [
    //     "old_postsmodeltemplate"  => ["PostsModelTemplate dépassé", "PostsModelTemplate dépassés"]
    // ];

    // protected $dateFormats = [
    //     "short"   => "d/m/Y"
    // ];

    public function __construct()
    {
        parent::__construct();

        $this->addFields([

        ]);
    }
}