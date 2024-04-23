<?php

namespace Syltaen;

class RowProcessor extends LayoutProcessor
{
    /**
     * The type of layout
     *
     * @var string
     */
    public $type = "row";

    /**
     * Processing of each section
     */
    public function process()
    {
        $this->settings = $this->data["layout_settings"];

        // Classes
        $this->addClasses([
            "flex-align-" . $this->settings["valign"],
            "gutters-" . $this->settings["spacing"],
            "mt-" . $this->settings["spacing_top"],
            "mb-" . $this->settings["spacing_bottom"],
            $this->settings["responsive"] != "none" ? $this->settings["responsive"] : "",
        ]);

        // Attributes
        if ($this->settings["animation"] != "none") {
            $this->setAttribute("data-bottom-top", "");
            $this->setAttribute("data-top-bottom", "");
        }

        // Columns
        $this->data["columns"] = set($this->data["columns"])->mapWithKey(function ($column, $i) {
            return (new ColumnProcessor($column, $this, $i))->getData();
        });

        $this->data["light"] = $this->data["columns"]->count() <= 1 && empty($this->data["attrs"]);
        return $this;
    }
}
