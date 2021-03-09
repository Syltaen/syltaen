<?php

namespace Syltaen;

class ContentsProcessor extends DataProcessor
{


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