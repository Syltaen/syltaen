<?php

namespace Syltaen;

class RowsProcessor extends DataProcessor
{
    /**
     * Processing of each section
     */
    public function process($row)
    {
        $settings = $row["layout_settings"];

        // Classes
        $row["classes"] = [
            "flex-align-" . $settings["valign"],
            $settings["spacing"] . "-gutters",
            $settings["spacing_top"] . "-margin-top",
            $settings["spacing_bottom"] . "-margin-bottom",
            $settings["responsive"] != "none" ? $settings["responsive"] : "",
        ];

        // Attributes
        $row["attrs"] = [];
        if ($settings["animation"] != "none") {
            $settings["attrs"]["data-bottom-top"] = "";
            $settings["attrs"]["data-top-bottom"] = "";
        }

        // Columns
        $row["columns"] = $this->processColumns($row["columns"], $settings);
        $row["light"]   = count($row["columns"]) <= 1 && $settings["animation"] == "none" && $settings["spacing_top"] == "no" && $settings["spacing_bottom"] == "no";

        return $row;
    }

    /**
     * Handle data for the "columns" content type
     *
     * @param  [type] $c
     * @return void
     */
    private function processColumns($columns, $settings)
    {
        $i = 0;

        return array_map(function ($col) use ($settings, &$i) {
            $col["styles"]  = [];
            $col["classes"] = [];

            // Proportions
            if ($col["layout_settings"]["width"] != 1) {
                $col["styles"][] = "flex: " . $col["layout_settings"]["width"] . ";";
            }

            // Animations
            if ($settings["animation"] != "none") {
                $col["classes"][] = "animation animation--" . $settings["animation"];

                if ($settings["delayed"]) {
                    $col["classes"][] = "delay-" . ($i++ * 2);
                }
            }

            // Content
            $col["content"] = (new ContentsProcessor($this->controller))->processEach($col["content"]);

            return $col;

        }, $columns);
    }
}