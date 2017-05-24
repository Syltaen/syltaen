<?php

namespace Syltaen\Models;


class Locations extends Posts
{

    const TYPE     = "locations";
    const LABEL    = "Locations";
    const ICON     = "dashicons-location";
    const HAS_PAGE = false;
    const SUPPORTS = ["title", "editor"];
    const TAX      = ["location-type"];

    protected $fields = [];

    public function __construct()
    {
        parent::__construct();
        $this->fields = array_merge($this->fields, [
            "coordonates"
        ]);
    }

    public function getByTypes()
    {

        $test = $this->taxonomy(true, true);

        /* #LOG# */ \Syltaen\Controllers\Controller::log($test);

        return $this->get();
    }
}