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
        $c["class"]       = "align-".$c["valign"];
        $c["txt_1_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_1"] : "gr-".substr($c['proportions'], 0, 1);
        $c["txt_2_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_2"] : "gr-".substr($c['proportions'], 2, 1);
    }

    /**
     * Handle data for the "3 columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private function txt_3col(&$c)
    {
        $c["class"]       = "align-".$c["valign"];
        $c["txt_1_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_1"] : "gr-".substr($c["proportions"], 0, 1);
        $c["txt_2_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_2"] : "gr-".substr($c["proportions"], 2, 1);
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
        $c["classes"] = ["full-width-image"];
        $c["styles"]  = [];
        $c["attr"]    = [];

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
        $s["class"] = "";
        $s["attr"]  = [];

        // ========== ID ========== //
        $s["attr"]["id"] = $s["section_ID"] ?: null;

        // ========== PADDING ========== //
        $s["class"] .= is_array($s["section_padding"]) ? join($s["section_padding"]) : $s["section_padding"];;


        // ========== BACKGROUND ========== //
        $s["class"] .= " bg-".$s["section_bg"];
        if ($s["section_bg"] == "image") {
            $s["attr"]["style"] = "background-image: url(".$s["section_bg_img"].");";
            $s["class"]        .= " size-".$s["section_bg_img_size"];
            $s["class"]        .= " position-".$s["section_bg_img_pos"];
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
        if (!$s["section_hide"]) return false;

        // BETWEEN TWO DATES
        if ($s["section_hide_after"] && $s["section_hide_before"]) {

            // SHOW BETWEEN TWO DATES
            if ($s["section_hide_after"] > $s["section_hide_before"]) {
                return time() < $s["section_hide_before"] || time() > $s["section_hide_after"];
            // HIDE BETWEEN TWO DATES
            } else {
                return time() < $s["section_hide_before"] && time() > $s["section_hide_after"];
            }

        // BEFORE A DATE
        } elseif ($s["section_hide_before"]) {
            return time() < $s["section_hide_before"];

        // AFTER A DATE
        } elseif ($s["section_hide_after"]) {
            return time() > $s["section_hide_after"];

        // ALWAYS HIDE
        } else {
            return true;
        }
    }


}