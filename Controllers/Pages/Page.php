<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Controllers\Parts\Sections;

class Page extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    const VIEW = "page";

    /**
     * Populate $this->data
     *
     * @param boolean $auto
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

    /**
     * Error 404 page display
     *
     * @return output HTML
     */
    public function error404()
    {
        echo $this->view("404");
    }
}