<?php

namespace Syltaen\Controllers\Pages;

use Syltaen\App\Services\Fields;
use Syltaen\Models\Posts\News;
use Syltaen\Controllers\Parts\Sections;

class Home extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    const VIEW = "home";

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
                (new News())
                    ->addThumbnailFormat("tag", "home", [310, 310])
                    ->get(3),
            "@news_more" => __("More info", "syltaen"),
            "news_after",
            "figures_before",
            "figures",
            "gates",
            "@sections" => (new Sections())->data(),
        ]);
    }
}