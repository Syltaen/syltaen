<?php

namespace Syltaen;

class Comments extends CommentsModel
{

    protected $dateFormats = [
        "short"   => "d/m/Y",
        "full"    => "d/m/Y \\à H\\hi"
    ];


    public function __construct()
    {
        parent::__construct();

        $this->fields = [
            "@author" => function ($comment) {

                // Has author
                if ($comment->user_id) {
                    return (new Users)->is($comment->user_id)->getOne();
                }

                // Fake author
                return (object) [
                    "display_name" => $comment->comment_author,
                    "photo"        => false,
                    "profile"      => false
                ];
            }
        ];
    }


}