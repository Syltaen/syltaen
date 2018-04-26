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

            "@aside"    => $this->aside(),

            "@sections" => SectionsProcessor::processEach(Data::get("sections")),

        ]);
    }


    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Handle sidebar
     *
     * @return array The aside data
     */
    protected function aside()
    {
        $aside = [];
        return $aside;
    }
}