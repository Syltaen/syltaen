<?php

namespace Syltaen;

class FieldStep extends \NF_Abstracts_Input
{
    protected $_name      = "fieldstep";
    protected $_section   = "layout";
    protected $_icon      = "exchange";
    protected $_templates = "tag";

    protected $_settings_only = ["label"];

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = "Étape";

        add_filter("nf_sub_hidden_field_types", function ($field_types) {
            $field_types[] = $this->_name;
            return $field_types;
        });

    }
}