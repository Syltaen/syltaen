<?php

namespace Syltaen;

class Pages extends PostsModel
{
    const TYPE     = "page";
    const LABEL    = "Pages";
    const HAS_PAGE = true;

    const HAS_EXCERPT = true;
    const HAS_THUMBNAIL         = true;
    const HAS_DEFAULT_THUMBNAIL = "images_placeholder_pages";

    /**
     * Get a page
     *
     * @return void
     */
    public function withPath($path)
    {
        return $this->is(get_page_by_path($path));
    }

    /**
     * Get the labal of the post type, allow for translations
     *
     * @param  bool     $singular
     * @return string
     */
    public static function getLabel($singular = false)
    {
        return __("Pages", "syltaen");
    }
}