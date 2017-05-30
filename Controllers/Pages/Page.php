<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Controllers\Parts\Sections;

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
     * @return void
     */
    public function ninjaFormPreview($form_id)
    {
        global $post;
        Fields::store($this->data, [
            "@intro_content" => "<h1>Form preview</h1>",
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


}