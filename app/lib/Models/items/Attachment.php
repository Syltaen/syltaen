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
     * @return bool
     */
    public function found()
    {
        return $this->getID() != 0;
    }

    /**
     * Get the file info
     *
     * @return array
     */
    public function getData()
    {
        $upload_dir = wp_upload_dir();
        $file       = $this->getMeta("_wp_attached_file");
        return (object) [
            "ID"   => $this->getID(),
            "name" => basename($file),
            "path" => $upload_dir["basedir"] . "/" . $file,
            "url"  => $this->url("thumbnail"),
            "size" => filesize($upload_dir["basedir"] . "/" . $file),
        ];
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

        return wp_get_attachment_image_url($this->getID(), $size) ?: wp_get_attachment_url($this->getID());
    }

    // =============================================================================
    // > IMAGES
    // =============================================================================

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

    // =============================================================================
    // > VIDEOS
    // =============================================================================

    /**
     * Get a video tag
     *
     * @param  string   $attributes
     * @return string
     */
    public function video($attributes = "")
    {
        return "<video src='" . $this->url() . "' $attributes></video>";
    }

    /**
     * Check that the attachment is a video
     *
     * @return boolean
     */
    public function isVideo()
    {
        $mime = isset($this->post_mime_type) ? $this->post_mime_type : get_post_mime_type($this->getID());
        return strpos($mime, "video") !== false;
    }
}