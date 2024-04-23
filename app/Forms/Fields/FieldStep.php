<?php

namespace Syltaen;

class FieldStep extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldstep";
    /**
     * @var string
     */
    protected $_section = "layout";
    /**
     * @var string
     */
    protected $_icon = "exchange";
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
        $this->_nicename = "Étape";

        add_filter("nf_sub_hidden_field_types", function ($field_types) {
            $field_types[] = $this->_name;
            return $field_types;
        });

    }
}