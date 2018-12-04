<?php

namespace Syltaen;

class ContentsProcessor extends DataProcessor
{


    /**
     * Handle data for the "columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private function columns(&$c)
    {
        $c["classes"]       = [
            "flex-align-" . $c["valign"],
            "flex-row--responsive-" . $c["responsive"],
            "flex-row--spacing-" . $c["spacing"],
        ];

        foreach ($c["columns"] as $i=>&$col) {
            $col["styles"]  = [];
            $col["classes"] = [];

            if ($c["custom_proportions"]) {
                $col["styles"][] = "flex: " . $col["width"] . ";";
            }

            if ($c["animation"] != "none") {
                $col["classes"][] = "animation-" . $c["animation"];

                if ($c["delayed"]) {
                    $col["classes"][] = "delay-" . $i;
                }
            }
        } unset($col);
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

    /**
     * Handle data for the archive content type.
     * Delegate to another processor : ArchiveProcessor
     *
     * @param [type] $c
     * @return void
     */
    private function archive(&$c)
    {
        $c = (new ArchiveProcessor($this->controller))->process($c);
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
    public function process($content)
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
                    $this->{$method}($content);
                }
        }

        return $content;
    }
}