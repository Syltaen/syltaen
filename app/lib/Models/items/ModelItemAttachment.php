<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemAttachment extends ModelItemPost
{
    /**
     * Get an attachment image URL with a spcific size
     *
     * @param string|array $size
     * @return string
     */
    public function url($size = "full")
    {
        return wp_get_attachment_image_url($this->getID(), $size);
    }

    /**
     * Get an attachment tag with a spcific size
     *
     * @param string|array $size
     * @return string
     */
    public function tag($size = "full")
    {
        return wp_get_attachment_image($this->getID(), $size);
    }
}