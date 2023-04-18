<?php

namespace Syltaen;

class ContentProcessor extends LayoutProcessor
{
    /**
     * The type of layout
     *
     * @var string
     */
    public $type = "content";

    /**
     * Handle data for the archive content type.
     * Delegate to another processor : ArchiveProcessor
     *
     * @param  [type] $c
     * @return void
     */
    private function archive()
    {
        (new ArchiveProcessor($this->data, $this->controller))
            ->setContent($this)
            ->{$this->data["list"]}();
    }


    // =============================================================================
    // > METHOD ROUTING
    // =============================================================================
    /**
     * Process each content
     *
     * @param  [type] $content
     * @return void
     */
    public function process()
    {
        $this->setAttributesFromSettings($this->data["layout_settings"]);

        // Run the correct mehtod by looking at the acf_fc_layout
        switch ($this->data["acf_fc_layout"]) {
            // Add custom layout-method routes here
            // Ex:
            // case "name-of-the-layout-1":
            // case "name-of-the-layout-2":
            //     static::nameOfTheMethod($content);
            //     break;

            // By default : convert - into _ and use the result as a method name
            default:
                $method = str_replace("-", "_", $this->data["acf_fc_layout"]);
                if (method_exists(static::class, $method)) {
                    $this->{$method}();
                }
        }

        return $this;
    }

    /**
     * Extra attributes to add based on the content settings
     *
     * @return array
     */
    public function setAttributesFromSettings($settings)
    {
        // Attributes
        if (!empty($settings["attrs"])) {
            $this->setAttributes((array) set($settings["attrs"])->index("name", "value"));
        }

        // Padding and margin
        foreach (["padding", "margin"] as $spacing) {
            foreach (["top", "bottom", "left", "right"] as $direction) {
                $this->setSpacing("{$spacing}-{$direction}", $settings["{$spacing}_{$direction}"]);
            }
        }

        // Background
        $this->setBackground(
            $settings["bg"] ?? false,
            $settings["bg_custom"] ?? false
        );

        // Text
        $this->setTextColor(
            $settings["text_color"]
        );
    }
}