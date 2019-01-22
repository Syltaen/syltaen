<?php

namespace Syltaen;

class SingleController extends BaseController
{

    protected $view = "single";

    /**
     * Page used for the post type archive
     *
     * @var WP_Post
     */
    protected $archive;

    /**
     * Populate $this->data
     */
    public function __construct($args = [])
    {
        parent::__construct($args);

        // Use the post type as a method
        $this->{$this->post->post_type}();

        // Populate & add the post to the context
        $this->model->populateResultData($this->post);
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
    private function news()
    {
        $this->model = new News;
        $this->view  = "single-news";
        $this->addSingleNav("Retour à la liste des news", "/");
    }


    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Add data for the navigation between posts
     *
     * @param string $archive_link_text Text to use for the archive link
     * @param string $archive_path The slug to the archive, default to post TYPE/REWRITE
     * @return void
     */
    private function addSingleNav($archive_link_text = false, $archive_path = false)
    {
        $this->addData([
            "@singlenav"  => [
                "archive"  => [
                    "url"  => site_url($archive_path ? $archive_path : ($this->model::CUSTOMPATH ? $this->model::CUSTOMPATH : $this->model::TYPE)),
                    "text" => $archive_link_text ?: __("Retour", "syltaen")
                ],
                "previous" => [
                    "url"  => get_previous_post() ? get_the_permalink(get_previous_post()->ID): "",
                    "text" => __("Précédent", "syltaen")
                ],
                "next" => [
                    "url"  => get_next_post() ? get_the_permalink(get_next_post()->ID): "",
                    "text" => __("Suivant", "syltaen")
                ]
            ]
        ]);
    }

}