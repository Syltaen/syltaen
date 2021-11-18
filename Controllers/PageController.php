<?php

namespace Syltaen;

class PageController extends BaseController
{

    /**
     * Handle context & rendering for content pages
     *
     * @return void
     */
    public function page()
    {
        $this->addData([
            "@intro_image"   => get_the_post_thumbnail_url(),
            "@intro_content" => "<h1>{$this->post->post_title}</h1>",
            "@sections"      => (new SectionsProcessor($this))->processEach(Data::get("sections")),
        ]);

        $this->render();
    }


    /**
     * Handle context & rendering for the homepage
     *
     * @return void
     */
    public function home()
    {
        $this->addData([

        ]);

        $this->render("home");
    }



    // ==================================================
    // > SPECIAL PAGES
    // ==================================================
    /**
     * Display a really simple page with a custom text
     *
     * @param [type] $content
     * @return void
     */
    public function simplePage($content)
    {
        $this->data["content"] = [[
            "acf_fc_layout" => "txt",
            "txt" => $content
        ]];

        $this->render("simple");
    }


    /**
     * Error 404 page display
     *
     * @return output HTML
     */
    public function error404()
    {
        global $pagename;

        // Make sure the error404 is set on the body
        $this->addBodyClass("error404");

        // Make sure the correcet header is set
        status_header("404");
        $this->render("404");
    }

    /**
     * Display a form
     *
     * @param int $form_id The ID of the form to display
     * @return void
     */
    public function ninjaFormPreview()
    {
        $this->simplePage("[ninja_form id=".$this->args[0]."]");
    }


    /**
     * Search results page
     *
     * @param string $search Terms to search for
     * @return output HTML
     */
    public function search($search = false)
    {
        $search = $search ?: $this->args["search"];

        $models_to_search = [
            new Pages,
            new News
        ];

        $this->data["results"] = [];
        $total_results_count   = 0;

        foreach ($models_to_search as $model) {
            $posts = $model->search($search)->get();
            $count = $model->count();
            if (!$count) continue;

            $this->data["results"][$model::TYPE] = [
                "posts" => $posts,
                "count" => sprintf(_n("1 résultat", "%s résultats", $count, "syltaen"), $count),
                "label" => $model::LABEL
            ];
            $total_results_count += $count;
        }

        $this->addData([
            "@title"       => __("Recherche pour : ", "syltaen")." <strong'>$search</strong>",
            "@search"      => $search
        ]);

        $this->addBodyClass("search-page");
        $this->render("search");
    }

}