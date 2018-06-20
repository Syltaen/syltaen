<?php

namespace Syltaen;

class SingleController extends PageController
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

        // Get the archive
        $this->archive = site_url($this->post->post_type);

        // Use the post type as a method
        $this->{$this->post->post_type}();

        // Erase post in render context
        $this->data["post"] = $this->post;

        // Set the view file
        $this->view = "single-{$this->post->post_type}";
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
        (new News)->populateResultData($this->post);

        Data::store($this->data, [
            "@singlenav"     => $this->singleNav(__("Retour à la liste des news", "syltaen"))
        ]);
    }


    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Add data for the navigation between posts
     *
     * @param string $archive_link_text Text to use for the archive link
     * @return void
     */
    private function singleNav($archive_link_text = false)
    {
        return [
            "archive"  => [
                "url"  => get_the_permalink($this->archive),
                "text" => $archive_link_text ?: __("Retour à la liste", "syltaen")
            ],
            "previous" => [
                "url"  => get_previous_post() ? get_the_permalink(get_previous_post()->ID): "",
                "text" => __("Précédent", "syltaen")
            ],
            "next" => [
                "url"  => get_next_post() ? get_the_permalink(get_next_post()->ID): "",
                "text" => __("Suivant", "syltaen")
            ]
        ];
    }

}