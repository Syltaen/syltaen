<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Controllers\Parts\Sections;
use Syltaen\Models\Posts\Pages;
use Syltaen\Models\Posts\News;
use Syltaen\Models\Posts\Jobs;
use Syltaen\Models\Posts\Press;

class Page extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    protected $view = "page";

    /**
     * Populate $this->data
     *
     * @param bool $spacial_page
     */
    public function __construct($special_page = false)
    {
        parent::__construct();
        $this->data = \Timber::get_context();


        if (!$special_page) {
            Fields::store($this->data, [
                "intro_content",
                "@sections" => (new Sections())->data()
            ]);
        }

    }

    // ==================================================
    // > SPECIAL PAGES
    // ==================================================
    /**
     * Error 404 page display
     *
     * @return output HTML
     */
    public function error404()
    {
        $this->render("404");
    }

    /**
     * Display a form
     *
     * @param int $form_id The ID of the form to display
     * @return output HTML
     */
    public function ninjaFormPreview($form_id)
    {
        global $post;
        Fields::store($this->data, [
            "@intro_content" => "<h1>".__("Form preview", "syltaen")."</h1>",
            "@sections"      => [[
                "classes" => "",
                "attr"    => "",
                "content" => [[
                    "acf_fc_layout" => "txt_1col",
                    "txt"           => do_shortcode("[ninja_form id=$form_id]")
                ]]
            ]]
        ]);

        $this->render();
    }

    /**
     * Search results page
     *
     * @param string $search Terms to search for
     * @return output HTML
     */
    public function search($search)
    {
        $models_to_search = [
            new Pages,
            new News,
            new Jobs,
            new Press
        ];

        $this->data["results"] = [];
        $total_results_count   = 0;

        foreach ($models_to_search as $model) {
            $this->data["results"][$model::TYPE] = [
                "posts" => $model->search($search)->get(),
                "count" => $model->count(),
                "label" => $model::LABEL
            ];
            $total_results_count += $model->count();
        }


        $total_results_count = $total_results_count > 1 ? $total_results_count." results" : ($total_results_count < 1 ? "No result" : "One result");

        $this->data["site"]->header["search"] = $search;
        Fields::store($this->data, [
            "@intro_content" => "<h1><span class='font-light'>".__("Search for : ", "syltaen")."</span> $search</h1><p>$total_results_count</p>"
        ]);
        $this->render("search");
    }


}