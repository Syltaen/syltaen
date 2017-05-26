<?php

namespace Syltaen\Models\Posts;

class Locations extends Posts
{

    const TYPE     = "locations";
    const LABEL    = "Locations";
    const ICON     = "dashicons-location";
    const HAS_PAGE = false;
    const SUPPORTS = ["title", "editor"];
    const TAX      = ["location-types"];

    public function __construct()
    {
        parent::__construct();
        $this->fields = [
            "coordonates"
        ];
    }
}