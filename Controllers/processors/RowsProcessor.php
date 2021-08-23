<?php

namespace Syltaen;

class RowsProcessor extends DataProcessor
{
    /**
     * Processing of each section
     */
    public function process($row)
    {
        // Classes
        $row["classes"] = [
            "flex-align-" . $row["valign"],
            $row["spacing"] . "-gutters",
            $row["spacing_top"] . "-margin-top",
            $row["spacing_bottom"] . "-margin-bottom",
            $row["responsive"] != "none" ? $row["responsive"] : ""
        ];

        // Attributes
        $row["attrs"] = [];
        if ($row["animation"] != "none") {
            $row["attrs"]["data-bottom-top"]  = "";
            $row["attrs"]["data-top-bottom"] = "";
        }

        // Columns
        $row["columns"] = $this->processColumns($row);
        $row["light"]   = count($row["columns"]) <= 1 && $row["animation"] == "none" && $row["spacing_top"] == "no" && $row["spacing_bottom"] == "no";

        return $row;
    }

    /**
     * Handle data for the "columns" content type
     *
     * @param [type] $c
     * @return void
     */
    private function processColumns($row)
    {
        $i = 0;

        return array_map(function ($col) use ($row, &$i) {

            $col["styles"]  = [];
            $col["classes"] = [];

            // Proportions
            if ($row["custom_proportions"]) {
                $col["styles"][] = "flex: " . $col["width"] . ";";
            }

            // Animations
            if ($row["animation"] != "none") {
                $col["classes"][] = "animation animation--" . $row["animation"];

                if ($row["delayed"]) {
                    $col["classes"][] = "delay-" . ($i++ * 2);
                }
            }

            // Content
            $col["content"] = (new ContentsProcessor($this->controller))->processEach($col["content"]);

            return $col;

        }, $row["columns"]);
    }


}