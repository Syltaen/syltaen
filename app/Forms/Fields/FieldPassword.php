<?php

namespace Syltaen;

class FieldPassword extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldpassword";

    /**
     * @var string
     */
    protected $_nicename = "Mot de passe";

    /**
     * @var string
     */
    protected $_section = "userinfo";

    /**
     * @var string
     */
    protected $_type = "text";

    /**
     * @var string
     */
    protected $_icon = "key";

    /**
     * @var string
     */
    protected $_templates = "password";

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = __("Password", "ninja-forms");

        // add_filter("nf_sub_hidden_field_types", [$this, "hide_field_type"]);
    }

    /**
     * @param  $field_types
     * @return mixed
     */
    public function hide_field_type($field_types)
    {
        $field_types[] = $this->_name;
        return $field_types;
    }
}
