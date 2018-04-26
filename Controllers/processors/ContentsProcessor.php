<?php

namespace Syltaen;

abstract class ContentsProcessor extends DataProcessor
{
    /**
     * Handle data for the "2 columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private static function txt_2col(&$c)
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
    private static function txt_3col(&$c)
    {
        static::txt_2col($c);
        $c["txt_3_class"] = $c["proportions"] == "custom" ? "gr-".$c["width_3"] : "gr-".substr($c["proportions"], 4, 1);
    }

    /**
     * Handle data for the archive content type.
     * Delegate to another processor : ArchiveProcessor
     *
     * @param [type] $c
     * @return void
     */
    private static function archive(&$c)
    {
        $c = ArchiveProcessor::process($c);
    }


    /**
     * Handle content for full-width images content type
     *
     * @param array $c
     * @uses Skrollr.js
     * @return void
     */
    private static function full_width_image(&$c)
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



    // =============================================================================
    // > METHOD ROUTING
    // =============================================================================
    /**
     * Process each content
     *
     * @param [type] $content
     * @return void
     */
    public static function process($content)
    {
        // Run the correct mehtod by looking at the acf_fc_layout
        switch ($content["acf_fc_layout"]) {

            // Add custom layout-method routes here
            // Ex:
            // case "name-of-the-layout-1":
            // case "name-of-the-layout-2":
            //     static::nameOfTheMethod($content);
            //     break;


            // By default : convert - into _ and use the result as a method name
            default:
                $method = str_replace("-", "_", $content["acf_fc_layout"]);
                if (method_exists(static::class, $method)) {
                    static::{$method}($content);
                }
        }

        return $content;
    }
}