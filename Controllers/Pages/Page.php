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
     */
    public function __construct()
    {
        parent::__construct();
        $this->data = \Timber::get_context();

        Fields::store($this->data, [
            "intro_content",
            "@sections" => (new Sections())->data()
        ]);

    }


    // ==================================================
    // > ERRORS
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
}