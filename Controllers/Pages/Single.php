<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Models\Posts\News;
use Syltaen\Models\Posts\Jobs;


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
     */
    public function __construct()
    {
        global $post;
        parent::__construct();
        $this->data    = \Timber::get_context();
        $this->post    = $post;
        $this->archive = get_page_by_path($post->post_type);

        switch($this->post->post_type) {
            case "news":
                $this->singleNews();
                break;
            case "jobs":
                $this->singleJobs();
                break;
            default: break;
        }

        $this->data["post"] = $this->post;

    }

    // ==================================================
    // > POST TYPES
    // ==================================================
    /**
     * Data and view handling for News
     *
     * @return void
     */
    private function singleNews()
    {
        $this->addIntroData();
        $this->addNavigationData(__("Back to the list of news", "syltaen"));

        (new News)->populatePostData($this->post);
    }

    /**
     * Data and view handling for Jobs
     *
     * @return void
     */
    private function singleJobs()
    {
        $this->addIntroData();
        $this->addNavigationData( __("Back to the list of jobs", "syltaen"));

        (new Jobs)->populatePostData($this->post);
        Fields::store($this->post, [
            "content",
            "application_form"
        ]);

        $this->view = "single-job";
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
     * @param string $archive_link_text Text to use for the archive link
     * @return void
     */
    private function addNavigationData($archive_link_text = false)
    {
        $this->data["singlenav"] = [
            "archive"  => [
                "url"  => get_the_permalink($this->archive),
                "text" => $archive_link_text ?: __("Back to the list", "syltaen")
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