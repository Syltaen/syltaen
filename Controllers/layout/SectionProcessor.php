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
        // Hide the section on demand
        if ($this->shouldHide()) {
            return false;
        }

        // Process attributes
        $this->processAttributes();

        // Process each rows
        $this->data["rows"] = set($this->data["rows"])->mapWithKey(function ($row, $i) {
            return (new RowProcessor($row, $this, $i))->getData();
        });

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

        // Padding
        foreach (["top", "bottom", "left", "right"] as $side) {
            $this->setSpacing("padding-{$side}", $this->data["padding_{$side}"]);
        }

        // Background
        $this->setBackground(
            $this->data["bg"],
            $this->data["bg_custom"]
        );

        // Background image
        if ($this->data["bg_img"]) {
            $this->data["image"] = [
                "style" => "background-image: url(" . $this->data["bg_img"] . ");",
                "class" => ["bg-image", "bg-image--" . $this->data["bg_img_size"], "bg-image--" . $this->data["bg_img_pos"]],
            ];
        }

        // Text color
        $this->setTextColor($this->data["text_color"]);

        // ID / Anchor
        if ($this->data["anchor"]) {
            $this->addAttribute("id", sanitize_title($this->data["anchor"]));
        }
    }

    /**
     * Check if the section should be hidden
     *
     * @param  array   $s The section's data
     * @return boolean : true if the section should be hidden
     */
    private function shouldHide()
    {
        if (empty($this->data["hide"])) {
            return false;
        }

        $time = current_time("timestamp");

        // BETWEEN TWO DATES
        if ($this->data["hide_start"] && $this->data["hide_end"]) {
            // SHOW BETWEEN TWO DATES
            if ($this->data["hide_start"] > $this->data["hide_end"]) {
                return $time < $this->data["hide_end"] || $time > $this->data["hide_start"];
                // HIDE BETWEEN TWO DATES
            } else {
                return $time < $this->data["hide_end"] && $time > $this->data["hide_start"];
            }

            // BEFORE A DATE
        } elseif ($this->data["hide_end"]) {
            return $time < $this->data["hide_end"];

            // AFTER A DATE
        } elseif ($this->data["hide_start"]) {
            return $time > $this->data["hide_start"];

            // ALWAYS HIDE
        } else {
            return true;
        }
    }
}