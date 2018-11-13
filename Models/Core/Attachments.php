<?php

namespace Syltaen;

class Attachments extends PostsModel
{
    const TYPE     = "attachment";
    const LABEL    = "Attachements";

    const HAS_THUMBNAIL = true;

    protected $thumbnailsFormats = [
        "url" => [
            "small" => "thumbnail"
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->fields = [
            "@url" => function ($attachment) {
                return Data::filter($attachment, "img:url");
            }
        ];

        $this->status("inherit");
    }

    // ==================================================
    // > FILTERS
    // ==================================================
    public function of($post_id)
    {
        $this->parent($post_id);
        return $this;
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

    /**
     * Add all thumbnail formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateThumbnailFormats(&$attachment)
    {
        if (!static::HAS_THUMBNAIL) return false;

        $attachment->thumb = [
            "url" => [],
            "tag" => []
        ];

        if (!empty($this->thumbnailsFormats["url"])) {
            foreach ($this->thumbnailsFormats["url"] as $name=>$format) {
                $attachment->thumb["url"][$name] = wp_get_attachment_image_src($attachment->ID, $format)[0];
            }
        }

        if (!empty($this->thumbnailsFormats["tag"])) {
            foreach ($this->thumbnailsFormats["tag"] as $name=>$format) {
                $attachment->thumb["tag"][$name] = wp_get_attachment_image($attachment->ID, $format);
            }
        }
    }
}