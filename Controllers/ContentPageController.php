<?php

namespace Syltaen;

class ContentPageController extends PageController
{
    /**
     * Populate the context
     */
    public function __construct($args = [])
    {
        parent::__construct($args);

        Data::store($this->data, [

            "intro_content",
            "(img:url) intro_bg",

            "@sections" => (new SectionsProcessor($this))->processEach(Data::get("sections")),

        ]);
    }
}