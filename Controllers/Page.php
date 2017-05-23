<?php

namespace Syltaen\Controllers;

use Syltaen\Models\ACF\Fields;
use Syltaen\Models\ACF\Sections;
use Syltaen\Models\News;

class Page extends Controller
{

    /**
     * Constructor
     *
     * @param boolean $auto
     */
    public function __construct()
    {
        parent::__construct();
        $this->data = \Timber::get_context();
    }

    /**
     * Home display
     *
     * @return HTML
     */
    public function home()
    {
        Fields::store($this->data, [
            "intro_content",
            "intro_image",
            "group_gate_left",
            "group_gate_right",
            "@news_last" => (new News())->get(),
            "@news_link" => site_url("news")
        ]);

        echo $this->view('home');
    }

    /**
     * Page display
     *
     * @return output HTML
     */
    public function page()
    {
        Fields::store($this->data, ["intro"]);
        // Sections::store($this->data);
        echo $this->view("page");
    }

    /**
     * Error 404 page display
     *
     * @return output HTML
     */
    public function error404()
    {
        $this->view("404");
    }

    /**
     * Single post's page display
     *
     * @return output HTML
     */
    public function single()
    {
        echo $this->view("single");
    }
}