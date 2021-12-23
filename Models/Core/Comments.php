<?php

namespace Syltaen;

class Comments extends CommentsModel
{
    /**
     * @var array
     */
    protected $dateFormats = [
        "short" => "d/m/Y",
        "full"  => "d/m/Y \\à H\\hi",
    ];
}