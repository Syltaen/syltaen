<?php

namespace Syltaen;

class FieldCloseTag extends \NF_Abstracts_Input
{
    protected $_name      = "fieldclosetag";
    protected $_section   = "layout";
    protected $_icon      = "chevron-down";
    protected $_templates = "tag";

    protected $_settings_only = [""];

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = "&nbsp;";

        add_filter("nf_sub_hidden_field_types", function ($field_types) {
            $field_types[] = $this->_name;
            return $field_types;
        });
    }

}