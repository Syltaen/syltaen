<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class Attachment extends Post
{
    /**
     * Check that this is not an empty image
     *
     * @return void
     */
    public function found()
    {
        return $this->getID() != 0;
    }

    /**
     * Get an attachment image URL with a spcific size
     *
     * @param  string|array $size
     * @return string
     */
    public function url($size = "full")
    {
        if (!$this->found()) {
            return "";
        }

        return wp_get_attachment_image_url($this->getID(), $size);
    }

    /**
     * Output the image as a background-image style attribute
     *
     * @return void
     */
    public function bg($size = "full")
    {
        if (!$this->found()) {
            return "";
        }

        return "background-image: url(" . $this->url($size) . ")";
    }

    /**
     * Get an attachment tag with a spcific size
     *
     * @param  string|array $size
     * @return string
     */
    public function tag($size = "full", $class = false)
    {
        if (!$this->found()) {
            return "";
        }

        $tag = wp_get_attachment_image($this->getID(), $size);

        if ($class) {
            $tag = str_replace("class=\"", "class=\"$class ", $tag);
        }

        return $tag;
    }
}