<?php

namespace Syltaen;

class SectionsController extends Controller
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
        $this->data = Data::get($key, $post_id, []);

        foreach ($this->data as $section_key=>&$section) {

            if ($this->shouldHide($section)) {
                unset($this->data[$section_key]);
                break;
            }

            $this->parameters($section);

            foreach ($section["content"] as &$content) {
                // Add data to the content by using the method matching the layout name
                $method = str_replace("-", "_", $content["acf_fc_layout"]);
                $this->$method($content);
            }
        }
    }



    // ==================================================
    // > SECTION CONTENT
    // ==================================================
    /**
     * Handle data for the "1 column" content type
     *
     * @param [type] $c
     * @return void
     */
    private function txt_1col(&$c)
    {
    }

    /**
     * Handle data for the "2 columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private function txt_2col(&$c)
    {
        $c["class"]       = "flex-row flex-row-wrap flex-align-".$c["valign"];
        $c["txt_1_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_1"] : "gr-".substr($c["proportions"], 0, 1);
        $c["txt_2_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_2"] : "gr-".substr($c["proportions"], 2, 1);
    }

    /**
     * Handle data for the "3 columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private function txt_3col(&$c)
    {
        $this->txt_2col($c);
        $c["txt_3_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_3"] : "gr-".substr($c["proportions"], 4, 1);
    }

    /**
     * Handle data for the archive content type.
     * Delegate to another controller : ArchiveController
     *
     * @param [type] $c
     * @return void
     */
    private function archive(&$c)
    {
        // get ArchiveController singleton
        $this->archiveController = isset($this->archiveControllernew) ? $this->archiveController : new ArchiveController();
        // use the "type" data as a method name to be called
        $this->archiveController->{$c["type"]}($c);
    }


    /**
     * Handle content for full-width images content type
     *
     * @param array $c
     * @uses Skrollr.js
     * @return void
     */
    private function full_width_image(&$c)
    {
        $c["attr"]    = [];
        $c["classes"] = ["full-width-image", $c["parallax"]];


        // ========== IMAGE ========== //
        $c["attr"]["style"] = "background-image: url(".$c["image"]["url"].");";
        $c["image"]    = wp_get_attachment_image($c["image"]["ID"], [1600, null]);

        // ========== PARALLAX ========== //
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
        $s["classes"] = ["site-section"];
        $s["attr"]    = [];

        // ========== ID ========== //
        $s["attr"]["id"] = $s["anchor"] ? sanitize_title($s["anchor"]) : null;

        // ========== PADDING ========== //
        $s["classes"][] = $s["padding"] . "-padding-vertical";

        // ========== BACKGROUND ========== //
        $s["classes"][] = "bg-" . $s["bg"];
        if ($s["bg"] == "image") {
            $s["attr"]["style"] = "background-image: url(" . $s["bg_img"] . ");";
            $s["classes"][]     = "bg-image--" . $s["bg_img_size"];
            $s["classes"][]     = "bg-image--" . $s["bg_img_pos"];
        }

        // ========== TEXT ========== //
        if ($s["text_color"] != "none") {
            $s["classes"][] = "color-" . $s["text_color"];
        }

        // ========== EDGES ========== //
        if ($s["top_edge"] != "none") {
            $s["classes"][] = "has-edge-top--" . $s["top_edge"];
            if ($s["top_edge_color"] != "section") {
                $s["classes"][] = "has-edge-top--" . $s["top_edge_color"];
            }
        }

        if ($s["bottom_edge"] != "none") {
            $s["classes"][] = "has-edge-bottom--" . $s["bottom_edge"];
            if ($s["bottom_edge_color"] != "section") {
                $s["classes"][] = "has-edge-bottom--" . $s["bottom_edge_color"];
            }
        }


    }

    /**
     * Check if the section should be hidden
     *
     * @param array $s The section's data
     * @return boolean : true if the section should be hidden
     */
    private function shouldHide($s)
    {
        if (!$s["hide"]) return false;

        // BETWEEN TWO DATES
        if ($s["hide_start"] && $s["hide_end"]) {

            // SHOW BETWEEN TWO DATES
            if ($s["hide_start"] > $s["hide_end"]) {
                return time() < $s["hide_end"] || time() > $s["hide_start"];
            // HIDE BETWEEN TWO DATES
            } else {
                return time() < $s["hide_end"] && time() > $s["hide_start"];
            }

        // BEFORE A DATE
        } elseif ($s["hide_end"]) {
            return time() < $s["hide_end"];

        // AFTER A DATE
        } elseif ($s["hide_start"]) {
            return time() > $s["hide_start"];

        // ALWAYS HIDE
        } else {
            return true;
        }
    }
}