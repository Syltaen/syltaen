<?php

namespace Syltaen;

class Pages extends PostsModel
{
    const TYPE  = "page";
    const LABEL = "Pages";

    /**
     * Get a page
     *
     * @return void
     */
    public function withPath($path)
    {
        return $this->is(get_page_by_path($path));
    }
}