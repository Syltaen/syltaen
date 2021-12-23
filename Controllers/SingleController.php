<?php

namespace Syltaen;

class SingleController extends PageController
{
    /**
     * @var string
     */
    public $view = "single";

    /**
     * Populate $this->data
     */
    public function __construct($args = [])
    {
        parent::__construct($args);

        // Use the post type as a method
        if (method_exists($this, $this->post->post_type)) {
            $this->{$this->post->post_type}();
            // Populate & add the post to the context
            $this->post         = new Post($this->post, $this->model);
            $this->data["post"] = $this->post;
        }
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
        $this->addSingleNav("Retour à la liste des news");
    }

    /**
     * Data and view handling for News
     *
     * @return void
     */
    private function attachment()
    {
        $this->simplePage(
            "<h2>{$this->post->post_title}</h2>" .
            wp_get_attachment_image($this->post->ID, "full")
        );
    }

    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Add data for the navigation between posts
     *
     * @param  string $archive_link_text Text to use for the archive link
     * @param  string $archive_path      The slug to the archive, default to post TYPE/REWRITE
     * @return void
     */
    private function addSingleNav($archive_link_text = false)
    {
        $this->addData([
            "@singlenav" => [
                "archive"  => [
                    "url"  => $this->model::getArchiveURL(),
                    "text" => $archive_link_text ?: __("Retour", "syltaen"),
                ],
                "previous" => [
                    "url"  => get_previous_post() ? get_the_permalink(get_previous_post()->ID) : "",
                    "text" => __("Précédent", "syltaen"),
                ],
                "next"     => [
                    "url"  => get_next_post() ? get_the_permalink(get_next_post()->ID) : "",
                    "text" => __("Suivant", "syltaen"),
                ],
            ],
        ]);
    }
}