<?php

namespace Syltaen;

class FieldRange extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldrange";

    /**
     * @var string
     */
    protected $_section = "common";

    /**
     * @var string
     */
    protected $_type = "range";

    /**
     * @var string
     */
    protected $_icon = "tachometer";

    /**
     * @var string
     */
    protected $_templates = "range";

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("Graduation numérique", "ninja-forms");

        $this->_settings["min"] = [
            "name"  => "min",
            "type"  => "number",
            "label" => __("Valeur minimum", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "0",
        ];

        $this->_settings["max"] = [
            "name"  => "max",
            "type"  => "number",
            "label" => __("Valeur maximum", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "100",
        ];

        $this->_settings["step"] = [
            "name"  => "step",
            "type"  => "number",
            "label" => __("Étape", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "1",
        ];
    }
}