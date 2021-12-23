<?php

namespace Syltaen;

class Posts extends PostsModel
{
    const TYPE  = "post";
    const LABEL = "Articles de blog";

    const HAS_THUMBNAIL = true;

    /**
     * @var array
     */
    protected $dateFormats = [
        "short" => "d/m/Y",
    ];

    public function __construct()
    {
        parent::__construct();

        $this->addFields([
            "@author"   => function ($product) {
                return (new Users)->is($product->post_author);
            },
            "@comments" => function ($product) {
                return (new Comments)->of($product->ID);
            },
        ]);
    }
}