<?php

namespace Syltaen;

class ColumnProcessor extends LayoutProcessor
{
    /**
     * The type of layout
     *
     * @var string
     */
    public $type = "column";

    /**
     * Processing of each section
     */
    public function process()
    {
        $this->settings = $this->data["layout_settings"];

        // Proportions
        if ($this->settings["width"] != 1) {
            $this->addStyle("flex", $this->settings["width"]);
        }

        // Animations
        if ($this->getRow()->settings["animation"] != "none") {
            $this->addClasses([
                "animation",
                "animation--" . $this->getRow()->settings["animation"],
            ]);

            if ($this->getRow()->settings["delayed"]) {
                $this->addClass("delay-" . ($this->index * 2));
            }
        }

        // Content
        $this->data["content"] = set($this->data["content"] ?: [])->mapWithKey(function ($content, $i) {
            return (new ContentProcessor($content, $this, $i))->getData();
        });

        return $this;
    }
}