<?php

namespace Syltaen\App\Services\Forms\Fields;

class LoginField extends \NF_Fields_Email
{
    protected $_name       = "loginfield";

    protected $_section    = "userinfo";

    protected $_type       = "email";

    protected $_icon       = "user";

    protected $_templates  = "email";

    protected $_test_value = "foo@bar.dev";


    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("Login", "ninja-forms");
    }


    /**
     * Set a default value when the user is logged in
     *
     * @param string $default_value
     * @param string $field_class
     * @param array $settings
     * @return string
     */
    public function filter_default_value($default_value, $field_class, $settings)
    {
        if (!isset($settings["default_type"]) || "user-meta" != $settings["default_type"] || $this->_name != $field_class->get_name()) return $default_value;

        $current_user = wp_get_current_user();

        if ($current_user) {
            $default_value = $current_user->user_email;
        }

        return $default_value;
    }


    /**
     * Validate
     *
     * @param $field
     * @param $data
     * @return array $errors
     */
    public function validate($field, $data)
    {
        $errors = parent::validate($field, $data);

        if (email_exists($field["value"])) {
            $errors[] = "Cette adresse e-mail est déjà utilisée";
        }

        return $errors;
    }


}