<?php

namespace Syltaen\Models;


class News extends Posts
{

    const TYPE     = "news";
    const LABEL    = "News";
    const ICON     = "dashicons-megaphone";
    const SUPPORTS = array("title", "editor", "excerpt", "thumbnail");

    protected $fields = [];

    /**
     * Create the base query and add all needed fields for parent::addFields()
     */
    public function __construct()
    {
        parent::__construct();
        // $this->fields = array_merge($this->fields, [

        // ]);
    }

}