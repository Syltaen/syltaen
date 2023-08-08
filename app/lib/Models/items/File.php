<?php

namespace Syltaen;

/**
 * API to manage file like attachments
 */
class File
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $mime;

    /**
     * Create a new file instances
     *
     * @param string url_or_path
     */
    public function __construct($url_or_path)
    {
        if (Text::startsWith($url_or_path, "http")) {
            $this->url  = $url_or_path;
            $this->path = str_replace(site_url(), rtrim(ABSPATH, "/"), $url_or_path);
        } else {
            $this->path = $url_or_path;
            $this->url  = str_replace(rtrim(ABSPATH, "/"), site_url(), $url_or_path);
        }
    }

    /**
     * Check that the file exists
     *
     * @return bool
     */
    public function found()
    {
        return file_exists($this->path);
    }

    /**
     * Get the file info
     *
     * @return array
     */
    public function getData()
    {
        $exists = file_exists($this->path);
        return (object) [
            "ID"   => false,
            "name" => $this->name ?? basename($this->path),
            "path" => $this->path,
            "url"  => $this->url,
            "size" => $exists ? filesize($this->path) : 0,
            "mime" => $this->mime ?? ($exists ? mime_content_type($this->path) : false),
        ];
    }

    /**
     * Get this file's url
     *
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * Get this file's path
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    // =============================================================================
    // > SETTERS
    // =============================================================================
    /**
     * Set a custom name for this file
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set a custom mime for this file
     * @param  string $mime
     * @return self
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
        return $this;
    }

    // =============================================================================
    // > IMAGES
    // =============================================================================
    /**
     * Output the image as a background-image style attribute
     *
     * @return void
     */
    public function bg()
    {
        return "background-image: url(" . $this->url() . ")";
    }

    /**
     * Get an attachment tag with a spcific size
     *
     * @param  string|array $size
     * @return string
     */
    public function tag($size = "full", $class = false)
    {
        return "<img src='" . $this->url() . "' class='{$class} image--{$size}'>";
    }

    /**
     * Check if the file is an image
     *
     * @return boolean
     */
    public function isImage()
    {
        $mime = !empty($this->path) ? mime_content_type($this->path) : "";
        return strpos($mime, "image") !== false;
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
        $mime = !empty($this->path) ? mime_content_type($this->path) : "";
        return strpos($mime, "video") !== false;
    }
}
