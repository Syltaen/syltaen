<?php

namespace Syltaen;

class FieldPassword extends \NF_Abstracts_Input
{
    protected $_name      = "fieldpassword";

    protected $_nicename  = "Mot de passe";

    protected $_section   = "userinfo";

    protected $_type      = "text";

    protected $_icon      = "key";

    protected $_templates = "password";

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = __("Password", "ninja-forms");

        // add_filter("nf_sub_hidden_field_types", [$this, "hide_field_type"]);
    }


    public function hide_field_type($field_types)
    {
        $field_types[] = $this->_name;
        return $field_types;
    }
}
