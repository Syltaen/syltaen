<?php

namespace Syltaen;

class FieldCloseTag extends \NF_Abstracts_Input
{
    protected $_name      = "fieldclosetag";
    protected $_section   = "layout";
    protected $_icon      = "chevron-right";
    protected $_templates = "tag";

    protected $_settings_only = ["label"];

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = "/end div";

        add_filter("nf_sub_hidden_field_types", function ($field_types) {
            $field_types[] = $this->_name;
            return $field_types;
        });
    }

}