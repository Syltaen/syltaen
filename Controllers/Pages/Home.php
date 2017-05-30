<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Models\Posts\News;
use Syltaen\Models\Posts\Jobs;
use Syltaen\Controllers\Parts\Sections;
use Syltaen\App\Services\Pagination;

class Home extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    protected $view = "home";

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
            "intro_image",
            "group_gate_left",
            "group_gate_right",
            "news_background",
            "news_before",
            "@news_last" =>
                (new News)
                    ->addThumbnailFormat("tag", "home", "medium")
                    ->addThumbnailFormat("url", "home", "medium")
                    ->get(3),
            "@news_more" => __("More info", "syltaen"),
            "news_after",
            "figures_before",
            "figures",
            "gates",
            "@sections" => (new Sections())->data(),
        ]);

        $test = (new Jobs)->get();
    }
}