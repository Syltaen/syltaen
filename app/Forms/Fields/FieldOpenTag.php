<?php

namespace Syltaen;

class FieldOpenTag extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldopentag";
    /**
     * @var string
     */
    protected $_section = "layout";
    /**
     * @var string
     */
    protected $_icon = "chevron-up";
    /**
     * @var string
     */
    protected $_templates = "tag";

    /**
     * @var array
     */
    protected $_settings_only = ["label"];

    /**
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();
        $this->_nicename                   = "gr-6 gr-12-sm";
        $this->_settings["label"]["label"] = "Classes CSS";

        $this->_settings["attrs"] = [
            "name"    => "attrs",
            "type"    => "option-repeater",
            "label"   => "Autres attributs <a href='#' class='nf-add-new'>Ajouter</a>",
            "width"   => "full",
            "group"   => "primary",
            "columns" => [
                "label" => [
                    'header'  => "Nom",
                    'default' => null,
                ],
                "value" => [
                    "header"  => "Valeur",
                    'default' => null,
                ],

            ],
        ];

        add_filter("nf_sub_hidden_field_types", function ($field_types) {
            $field_types[] = $this->_name;
            return $field_types;
        });
    }
}