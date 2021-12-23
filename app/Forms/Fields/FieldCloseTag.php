<?php

namespace Syltaen;

class FieldCloseTag extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldclosetag";
    /**
     * @var string
     */
    protected $_section = "layout";
    /**
     * @var string
     */
    protected $_icon = "chevron-down";
    /**
     * @var string
     */
    protected $_templates = "tag";

    /**
     * @var array
     */
    protected $_settings_only = [""];

    /**
     * @return mixed
     */
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