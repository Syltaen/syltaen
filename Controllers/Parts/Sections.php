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

            // ========== TXT 3 COL ========== //
            case "txt_3col":
                $c["class"]       = "align-".$c["valign"];
                $c["txt_1_class"] = "gr-".substr($c['proportions'], 0, 1);
                $c["txt_2_class"] = "gr-".substr($c['proportions'], 2, 1);
                $c["txt_3_class"] = "gr-".substr($c['proportions'], 4, 1);
                break;

            // ========== ARCHIVE ========== //
            case "archive":
                $this->contentArchives($c, $s);
                break;

            // ========== CONTACT BLOCKS ========== //
            case "contact_info":
                break;

            // ========== FULL WIDTH IMAGE ========== //
            case "full_width_image":
                $this->contentFullWidthImage($c);
                break;
            default: break;
        }
    }

    /**
     * Handle content for Archives
     *
     * @param array $c the archive content
     * @param array $s the section
     * @return void
     */
    private function contentArchives(&$c, $s)
    {
        $pagination_model = false;

        switch($c["type"]) {
            case "news":
                $pagination_model = new News;
                $c["more"]        = __("More info", "syltaen");
                break;

            case "jobs":
                $pagination_model = new Jobs;
                $c["more"]        = __("More info", "syltaen");
                break;

            case "press":
                $pagination_model = new Press;
                $c["more"]        = __("See more", "syltaen");
                break;

            case "locations":
                $c["location_types"] = (new LocationTypes)->getPosts(new Locations);
                wp_enqueue_script("google.maps", "https://maps.googleapis.com/maps/api/js?key=AIzaSyBqGY0yfAyCACo3JUJbdgppD2aYcgV8sC0");
                break;
            default: break;
        }

        if ($pagination_model) {
            $pagination  = new Pagination($pagination_model, $c["perpage"]);
            $c["posts"]  = $pagination->posts();
            $c["walker"] = $pagination->walker("#".$s["attr"]["id"]);
        }
    }

    /**
     * Handle content for full-width images
     *
     * @param array $c
     * @return void
     */
    private function contentFullWidthImage(&$c) {
        $c["classes"] = ["full-width-image"];
        $c["styles"]  = [];
        $c["attr"]   = [];
        // ========== CLASSES ========== //
        $c["classes"][] = $c["parallax"];

        if ($c["top_edge"] != "default") {
            $c["classes"][] = $c["top_edge"];
        }

        if ($c["bottom_edge"] != "default") {
            $c["classes"][] = $c["bottom_edge"];
        }

        $c["classes"] = join($c["classes"], " ");

        // ========== STYLES ========== //
        $c["styles"][] = "background-image: url(".$c["image"]["url"].");";
        $c["image"]    = wp_get_attachment_image($c["image"]["ID"], [1600, null]);

        if ($c["vertical_offset"]) {
            $c["styles"][] = "margin-top: ".$c["vertical_offset"]."px;";
        }

        $c["styles"]  = join($c["styles"], " ");

        // ========== ATTRS ========== //
        switch ($c["parallax"]) {
            case "parallax-to-top":
                $c["attr"]["data-top-bottom"] = "background-position-y: 100%";
                $c["attr"]["data-bottom-top"] = "background-position-y: 0%";
                break;
            case "parallax-to-bottom":
                $c["attr"]["data-top-bottom"] = "background-position-y: 0%";
                $c["attr"]["data-bottom-top"] = "background-position-y: 100%";
                break;
            default: break;
        }
    }

}