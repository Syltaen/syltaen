<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Models\Posts\News;


class Single extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    protected $view = "single";

    /**
     * Page used for the post type archive
     *
     * @var WP_Post
     */
    protected $archive;

    /**
     * The global post
     *
     * @var WP_Posts
     */
    protected $post;


    /**
     * Populate $this->data
     *
     */
    public function __construct()
    {
        global $post;
        parent::__construct();
        $this->data    = \Timber::get_context();
        $this->post    = $post;
        $this->archive = get_page_by_path($post->post_type);

        $this->addIntroData();
        $this->addNavigationData();

        // Populate the post with the post type's model
        switch($this->post->post_type) {
            case "news":
                (new News)->populatePostData($this->post);
                $this->data["singlenav"]["archive"]["text"] = __("Back to the list of news", "syltaen");
                break;
            default: break;
        }

        $this->data["post"] = $this->post;

    }

    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Add data for the introduction
     * Get the intro_content from the post's archive
     * @return void
     */
    private function addIntroData()
    {
        $this->data["intro_content"] = Fields::get("intro_content", $this->archive);
    }

    /**
     * Add data for the navigation between posts
     *
     * @return void
     */
    private function addNavigationData()
    {
        $this->data["singlenav"] = [
            "archive"  => [
                "url"  => get_the_permalink($this->archive),
                "text" => __("Back to the list", "syltaen")
            ],
            "previous" => [
                "url"  => get_previous_post() ? get_the_permalink(get_previous_post()->ID): "",
                "text" => __("Previous", "syltaen")
            ],
            "next" => [
                "url"  => get_next_post() ? get_the_permalink(get_next_post()->ID): "",
                "text" => __("Next", "syltaen")
            ]
        ];
    }



}