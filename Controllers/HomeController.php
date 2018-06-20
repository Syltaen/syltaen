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



        ]);
    }

}