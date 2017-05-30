<?php

namespace Syltaen\App\Services\Forms\Actions;

if (!defined("ABSPATH") || !class_exists("NF_Abstracts_Action")) exit;


final class RegisterUser extends \NF_Abstracts_Action
{
    /**
     * @var string
     */
    protected $_name  = "registeruser";

    /**
     * @var array
     */
    protected $_tags = [];

    /**
     * @var string
     */
    protected $_timing = "late";

    /**
     * @var int
     */
    protected $_priority = 10;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("New user registration", "syltaen");

        $this->_settings["registration_user_success"] = [
            "name"           => "registration_user_success",
            "type"           => "rte",
            "group"          => "primary",
            "label"          => __("Success message", "syltaen"),
            "placeholder"    => "",
            "value"          => "",
            "width"          => "full",
            "use_merge_tags" => false
        ];
    }

    /**
     * Save the data
     *
     * @param array $action_settings
     * @return void
     */
    public function save($action_settings)
    {

    }

    /**
     * Process the data
     *
     * @param array $action_settings
     * @param int $form_id
     * @param array $data
     * @return void
     */
    public function process($action_settings, $form_id, $data)
    {
        // ==================================================
        // > VALUES
        // ==================================================
        $val = [];
        foreach ($data["fields"] as $field) {
            if ($field["key"]) $val[$field["key"]] = $field["value"];
        }


        // ==================================================
        // > INSERT USER
        // ==================================================
        $user_ID = wp_insert_user(array(
            'user_login'	=>  $val['login'],
            'user_pass'		=>  $val['password'],
            'user_nicename'	=>  urlencode($val['firstname']."-".$val['name']),
            'display_name'	=>  $val['firstname']." ".$val['name'],
            'nickname'		=>  $val['login'],
            'first_name'	=>  $val['firstname'],
            'last_name'		=>  $val['name'],
            'user_email'	=>  $val['login'],
            'show_admin_bar_front' => "false"
        )) ;

        update_field("reseau", $val["reseau"], "user_".$user_ID);


        // ==================================================
        // > SUCCESS MESSAGE
        // ==================================================
        if (isset($action_settings["registration_user_success"])) {
            $data["actions"]["success_message"] = do_shortcode( $action_settings["registration_user_success"] );
        }

        return $data;
    }
}
