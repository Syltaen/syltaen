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
     * @return object
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
            "mime" => mime_content_type($upload_dir["basedir"] . "/" . $file),
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

    /**
     * Get the filepath for this attachment
     *
     * @return string
     */
    public function path()
    {
        return get_attached_file($this->getID());
    }

    /**
     * Send the file as a download
     *
     * @return void
     */
    public function download()
    {
        $data = $this->getData();

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $data->name);
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        header("Content-Length: " . $data->size);
        ob_clean();
        flush();
        readfile($data->path);
        exit;
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

    /**
     * Check if the file is an image
     *
     * @return boolean
     */
    public function isImage()
    {
        return wp_attachment_is("image", $this->ID);
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
        return wp_attachment_is("video", $this->ID);
    }

    // =============================================================================
    // > GLOBAL CHECKS
    // =============================================================================
    /**
     * Get all the uses of this attachment
     *
     * @return array
     */
    public function getUses()
    {
        $id                = $this->getID();
        $filename          = pathinfo($this->path(), PATHINFO_FILENAME);
        $excluded_metakeys = ["_wp_attached_file", "_wp_attachment_metadata", "amazonS3_cache"];

        // Query different tables to check for uses of this attachment
        $uses = [

            // In posts
            "posts"             => Database::get_results("SELECT ID, post_content, post_excerpt FROM posts WHERE post_content LIKE '%$filename%' OR post_excerpt LIKE '%$filename%'"),

            // In postmeta
            "postmeta"          => Database::get_results(
                "SELECT post_id, meta_key, meta_value FROM postmeta
                WHERE (meta_value LIKE \"%$filename%\" OR meta_value = $id) AND meta_key NOT IN " . Database::inArray($excluded_metakeys)
            ),

            // In term descriptions
            "term_descriptions" => Database::get_results("SELECT term_id, taxonomy, description FROM term_taxonomy WHERE description LIKE \"%$filename%\""),

            // In termmeta
            "termmeta"          => Database::get_results(
                "SELECT tm.term_id term_id, tm.meta_key meta_key, tm.meta_value meta_value, tt.taxonomy taxonomy FROM termmeta tm
                 JOIN term_taxonomy tt ON tt.term_id = tm.term_id
                WHERE (meta_value LIKE \"%$filename%\" OR meta_value = $id)"
            ),

            // In options
            "options"           => Database::get_results("SELECT option_name, option_value FROM options WHERE option_value LIKE \"%$filename%\""),
        ];

        // Merge all into a readable list
        return array_merge(
            (array) $uses["posts"]->map(function ($row) {
                return ["[$row->ID] " . get_the_title($row->ID), "Contenu", get_edit_post_link($row->ID)];
            }),
            (array) $uses["postmeta"]->map(function ($row) {
                return ["[$row->post_id] " . get_the_title($row->post_id), "Meta : $row->meta_key", get_edit_post_link($row->post_id)];
            }),
            (array) $uses["term_descriptions"]->map(function ($row) {
                $term = get_term((int) $row->term_id, $row->taxonomy);
                return ["[$term->term_id] $term->name ($term->taxonomy)", "Description", get_edit_term_link($term->term_id, $term->taxonomy)];
            }),
            (array) $uses["termmeta"]->map(function ($row) {
                $term = get_term((int) $row->term_id, $row->taxonomy);
                return ["[$term->term_id] $term->name ($term->taxonomy)", "Meta : $row->meta_key", get_edit_term_link($term->term_id, $term->taxonomy)];
            }),
            (array) $uses["options"]->map(function ($row) {
                return ["Option", "$row->option_name", false];
            }),
        );
    }
}