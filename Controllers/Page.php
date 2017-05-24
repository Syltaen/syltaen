<?php

namespace Syltaen\Controllers;

use Syltaen\App\Services\Fields;
use Syltaen\Models\Sections;
use Syltaen\Models\News;
use Syltaen\Models\Locations;

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
            "news_background",
            "news_before",
            "@news_last" =>
                (new News())
                    ->addThumbnailFormat("tag", "home", [310, 310])
                    ->get(3),
            "@news_more" => __("More info", "syltaen"),
            "news_after",
            "figures_before",
            "figures",
            "gates",
            "@sections" => (new Sections())->data(),
            "pins" =>
                (new Locations())
                    ->getByTypes()
        ]);

        echo $this->view("home");
    }

    /**
     * Page display
     *
     * @return output HTML
     */
    public function page()
    {
        Fields::store($this->data, [
            "intro",
            "@sections" => (new Sections())->data()
        ]);

        echo $this->view("page");
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