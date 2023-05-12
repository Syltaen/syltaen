<?php

namespace Syltaen;

class SectionProcessor extends LayoutProcessor
{
    /**
     * The type of layout
     *
     * @var string
     */
    public $type = "section";

    /**
     * Processing of each section
     */
    public function process()
    {
        if ($this->data["acf_fc_layout"] == "include") {
            return $this->getInclude($this->data["include"]);
        }

        // Process attributes
        $this->processAttributes();

        // Process each rows
        $this->data["rows"] = set($this->data["rows"] ?: [])->mapWithKey(function ($row, $i) {
            return (new RowProcessor($row, $this, $i))->getData();
        });

        return $this;
    }

    /**
     * Get included section(s) data
     *
     * @param  int    $id
     * @return self
     */
    public function getInclude($id)
    {
        $this->data = [
            "sections" => set(Data::get("sections", $id, []))->mapAssoc(function ($i, $section) {
                $section = (new SectionProcessor($section, $this->controller, $i))->getData();
                if (isset($section["sections"])) {
                    return (array) $section["sections"];
                }

                return [uniqid() => $section];
            })
        ];
        return $this;
    }

    /**
     * Process a section's parameters
     *
     * @return void
     */
    private function processAttributes()
    {
        $this->addClass("site-section");

        $settings = $this->data["layout_settings"];

        // Padding
        foreach (["top", "bottom", "left", "right"] as $side) {
            if ($settings["padding_{$side}"] != "no") {
                $this->setSpacing("padding-{$side}", $settings["padding_{$side}"]);
            }
        }

        // Background
        $this->setBackground(
            $settings["bg"],
            $settings["bg_custom"]
        );

        // Side images
        if ($settings["bg_img_padding"]) {
            if ($settings["bg_img_left"]) {
                $this->addClass("site-section--img-left-" . $settings["bg_img_left_width"]);
            }
            if ($settings["bg_img_right"]) {
                $this->addClass("site-section--img-right-" . $settings["bg_img_right_width"]);
            }
        }

        // Text color
        $this->setTextColor($settings["text_color"]);

        // ID / Anchor
        if ($settings["anchor"]) {
            $this->addAttribute("id", sanitize_title($settings["anchor"]));
        }

        // Custom classes
        if ($settings["classes"]) {
            $this->addClass($settings["classes"]);
        }
    }
}
