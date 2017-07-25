<?php

namespace Syltaen;

class ArchiveController extends Controller
{
    /**
     * Archives for News
     *
     * @param array $c Local context
     * @return void
     */
    public function news(&$c)
    {
        $pagination   = (new Pagination(new News, $c["perpage"]));
        $c["walker"]  = $pagination->walker();
        $c["posts"]   = $pagination->posts();
    }

}