<?php

namespace Syltaen;

class SectionsProcessor extends DataProcessor
{
    /**
     * Processing of each section
     */
    public function process($section)
    {
        if ($this->shouldHide($section)) return false;

        $this->addAttributes($section);
        $this->addClasses($section);

        $section["rows"] = (new RowsProcessor($this->controller))->processEach($section["rows"]);

        return $section;
    }


    /**
     * Process a section's parameters
     *
     * @param array $s The section data
     * @return void
     */
    private function addClasses(&$s)
    {
        $s["classes"] = ["site-section"];

        // ========== PADDING ========== //
        if ($s["padding_top"] != "no") $s["classes"][] = $s["padding_top"] . "-padding-top";
        if ($s["padding_bottom"] != "no") $s["classes"][] = $s["padding_bottom"] . "-padding-bottom";

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

    private function addAttributes(&$s)
    {
        $s["attr"]    = empty($s["attr"]) ? [] : $s["attr"];

        // ========== ID ========== //
        $s["attr"]["id"] = $s["anchor"] ? sanitize_title($s["anchor"]) : null;

        // ========== PARALLAX ========== //
        if ($s["bg"] == "image" && $s["bg_img_pos"] == "parallax") {
            $s["attr"]["data-top-bottom"] = "background-position-y: 100%";
            $s["attr"]["data-bottom-top"] = "background-position-y: 0%";
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
        if (empty($s["hide"])) return false;

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