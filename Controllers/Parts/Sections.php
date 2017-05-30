<?php

namespace Syltaen\Controllers\Parts;

use Syltaen\Models\Posts\News;
use Syltaen\Models\Posts\Jobs;
use Syltaen\Models\Posts\Press;
use Syltaen\Models\Posts\Locations;
use Syltaen\Models\Taxonomies\LocationTypes;
use Syltaen\App\Services\Pagination;

class Sections extends \Syltaen\Controllers\Controller
{

    /**
     * Default view to use
     */
    protected $view = "parts/_sections";

    /**
     * Populate $this->data
     *
     * @param string $key The acf key in which the fields are stored
     * @param int $post_id The post in which the fields are stored
     */
    public function __construct($key = "sections", $post_id = null)
    {
        $this->data = get_field($key);
        foreach ($this->data as $section_key=>&$section) {

            if ($section["section_hide"]) {
                unset($this->data[$section_key]);
                break;
            }

            $this->parameters($section);

            foreach ($section["content"] as &$content) {
                $this->content($content, $section);
            }
        }
    }


    // ==================================================
    // > GLOBAL SECTION PARAMETERS
    // ==================================================
    /**
     * Process a section's parameters
     *
     * @param array $s The section data
     * @return void
     */
    private function parameters(&$s)
    {
        $s["classes"] = [];
        $s["attr"]    = [];

        // ========== ID ========== //
        $s["attr"]["id"] = $s["section_ID"] ?: null;

        // ========== PADDING ========== //
        $s["classes"][] = $s["section_padding"];

        // ========== BACKGROUND ========== //
        $s["classes"][] = "bg-".$s["section_bg"];
        if ($s["section_bg"] == "image") {
            $s["attr"]["style"] = "background-image: url(".$s["section_bg_img"].");";
            $s["classes"][] = "size-".$s["section_bg_img_size"];
            $s["classes"][] = "position-".$s["section_bg_img_pos"];
        }
    }

    // ==================================================
    // > SECTION CONTENT DATA
    // ==================================================
    /**
     * Precess a content stored in a section
     *
     * @param arrat $c The content data
     * @return void
     */
    private function content(&$c, $s)
    {
        switch ($c["acf_fc_layout"]) {

            // ========== TXT 1 COL ========== //
            case "txt_1col":
                break;

            // ========== TXT 2 COL ========== //
            case "txt_2col":
                $c["class"]       = "align-".$c["valign"];
                $c["txt_1_class"] = "gr-".substr($c['proportions'], 0, 1);
                $c["txt_2_class"] = "gr-".substr($c['proportions'], 2, 1);
                break;

            // ========== ARCHIVE ========== //
            case "archive":
                $this->contentArchives($c, $s);
                break;

            // ========== CONTACT BLOCKS ========== //
            case "contact_info":
                break;


            default: break;
        }
    }

    /**
     * Handle content for Archives
     *
     * @param array $a the archive content
     * @param array $s the section
     * @return void
     */
    private function contentArchives(&$a, $s)
    {
        $pagination_model = false;

        switch($a["type"]) {
            case "news":
                $pagination_model = new News;
                $a["more"]        = __("More info", "syltaen");
                break;

            case "jobs":
                $pagination_model = new Jobs;
                $a["more"]        = __("More info", "syltaen");
                break;

            case "press":
                $pagination_model = new Press;
                $a["more"]        = __("See more", "syltaen");
                break;

            case "locations":
                $a["location_types"] = (new LocationTypes)->getPosts(new Locations);
                wp_enqueue_script("google.maps", "https://maps.googleapis.com/maps/api/js?key=AIzaSyBqGY0yfAyCACo3JUJbdgppD2aYcgV8sC0");
                break;
            default: break;
        }

        if ($pagination_model) {
            $pagination  = new Pagination($pagination_model, $a["perpage"]);
            $a["posts"]  = $pagination->posts();
            $a["walker"] = $pagination->walker("#".$s["attr"]["id"]);
        }
    }


}