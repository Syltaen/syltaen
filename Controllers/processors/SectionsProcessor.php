<?php

namespace Syltaen;

abstract class SectionsProcessor extends DataProcessor
{
    /**
     * Processing of each section
     */
    public static function process($section)
    {
        if (static::shouldHide($section)) return false;

        static::addClasses($section);
        static::addAttributes($section);

        $section["content"] = ContentsProcessor::processEach($section["content"]);

        return $section;
    }


    /**
     * Process a section's parameters
     *
     * @param array $s The section data
     * @return void
     */
    private static function addClasses(&$s)
    {
        $s["classes"] = ["site-section"];

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

    private static function addAttributes(&$s)
    {
        $s["attr"]    = [];

        // ========== ID ========== //
        $s["attr"]["id"] = $s["anchor"] ? sanitize_title($s["anchor"]) : null;
    }

    /**
     * Check if the section should be hidden
     *
     * @param array $s The section's data
     * @return boolean : true if the section should be hidden
     */
    private static function shouldHide($s)
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