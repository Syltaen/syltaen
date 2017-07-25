<?php

namespace Syltaen;

class HomeController extends PageController
{

    protected $view = "home";

    /**
     * Populate the context
     */
    public function __construct($args = [])
    {
        parent::__construct($args);

        Data::store($this->data, [

            "intro" => $this->intro(),

            // content
            "@sections" => (new SectionsController)->data(),

        ]);
    }

    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Handle and return the data for the home intro
     *
     * @return array $data
     */
    private function intro()
    {
        $intro = [];
        return $intro;
    }
}