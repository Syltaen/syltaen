<?php

namespace Syltaen;

class Attachments extends PostsModel
{
    const TYPE       = "attachment";
    const LABEL      = "Attachements";
    const ITEM_CLASS = "ModelItemAttachment";

    const HAS_THUMBNAIL = true;

    public function __construct()
    {
        parent::__construct();

        $this->addGlobals([
            "@upload_dir" => function () {
                return wp_upload_dir();
            }
        ]);

        $this->addFields([
            "@url" => function ($attachment) {
                return Data::filter($attachment, "img:url");
            },

            /**
             * Path to the image
             */
            "_wp_attached_file",
            "@path" => function ($attachment) {
                return $this->upload_dir["basedir"] . "/" . $attachment->_wp_attached_file;
            },

            /**
             * Metadata & sizes
             */
            "_wp_attachment_metadata@metadata",
            "@sizes" => function ($attachment) {
                $sizes = $attachment->metadata["sizes"] ?? [];

                $sizes = array_merge(["full" => [
                    "width"  => $attachment->metadata["width"] ?? false,
                    "height" => $attachment->metadata["height"] ?? false,
                    "file"   => basename($attachment->metadata["file"]) ?? false,
                ]], $sizes);

                return array_map(function ($size) use ($attachment) {
                    $size["url"]  = dirname($attachment->url) . "/" . $size["file"];
                    $size["path"] = dirname($attachment->path) . "/" . $size["file"];
                    return $size;
                }, $sizes);
            }
        ]);

        $this->status("inherit");
    }

    // ==================================================
    // > FILTERS
    // ==================================================
    public function of($post_id)
    {
        return $this->parent($post_id);
    }

    /**
     * Filter based on encoded data returned by an UploadField
     *
     * @param string $json_encoded
     * @return self
     */
    public function fromEncoded($json_encoded)
    {
        return $this->is(json_decode(stripslashes($json_encoded)));
    }


    /**
     * Filter all medias that are not offloaded
     *
     * @return self
     */
    public function notOffloaded()
    {
        return $this->isnt(
            Database::get_col("SELECT source_id FROM as3cf_items")
        );
    }


    /**
     * Filter all images who where scaled down by WordPress
     *
     * @return void
     */
    public function whoWhereScaled()
    {
        return $this->meta("_wp_attached_file", "-scaled", "LIKE");
    }


    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Attach an attachment to a post
     *
     * @param int $parent_id
     * @return self
     */
    public function attach($parent_id)
    {
        $this->update([
            "post_parent" => $parent_id
        ]);
        return $this;
    }


    // ==================================================
    // > MODEL UPDATES
    // ==================================================
    /**
     * Prevent the registering of this post type, as it is already by default
     *
     * @return false
     */
    public static function register()
    {
        return false;
    }

    // ==================================================
    // > TOOLS
    // ==================================================

    /**
     * Check that all attachments are found at the right path
     *
     * @return void
     */
    public function getMissingFiles()
    {
        return array_filter(array_map(function ($sizes) {
            return array_filter(array_map(function ($size) {
                return file_exists($size["path"]) ? false : $size["path"];
            }, $sizes));
        }, $this->sizes));
    }

    /**
     * Check that all attachments are found at the right path
     *
     * @return void
     */
    public function get404()
    {
        return array_filter(array_map(function ($sizes) {
            return array_filter(array_map(function ($size) {
                $headers = get_headers($size["url"] . "?t=" . time(), true);
                if ($headers[0] == "HTTP/1.1 200 OK") return false;
                return [
                    $size["url"],
                    $headers[0]
                ];
            }, $sizes));
        }, $this->sizes));
    }


    /**
     * Regenerate the sizes for the matching media
     *
     * @need Plugin : Regenerate Thumbnails
     * @return array of results
     */
    public function regenerateThumbnails()
    {
        $this->regenerateThumbnailsResults = $this->map(function ($attachment) {
            return \RegenerateThumbnails_Regenerator::get_instance($attachment->ID)->regenerate();
        });

        return $this;
    }

    /**
     * Force the optimization through EWWW for all matching media
     *
     * @param bool $all_sizes Include all the sizes of the image
     * @return array of results
     */
    public function optimize($all_sizes = true)
    {
        global $ewww_force;
        $ewww_force = true;

        $this->optimizeResults = $this->map(function ($attachment) use ($all_sizes) {
            $results = [];
            foreach ($attachment->sizes as $size=>$data) {
                $results[$size] = ($size != "full" && !$all_sizes) ? false :  ewww_image_optimizer($data["path"], 4, false, $size == "full");
            }
            return $results;
        });

        return $this;
    }
}