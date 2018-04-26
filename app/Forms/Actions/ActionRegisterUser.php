<?php

namespace Syltaen;

class ActionRegisterUser extends \NF_Abstracts_Action
{
    /**
     * @var string
     */
    protected $_name  = "actionregisteruser";

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

        $this->_nicename = __("Inscription utilisateur", "syltaen");

        $this->_settings["success_message"] = [
            "name"           => "success_message",
            "type"           => "rte",
            "group"          => "primary",
            "label"          => __("Success message", "ninja-forms"),
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
        // > REGISTER USER
        // ==================================================
        $user_id = Users::add($val["email"], $val["password"], $val["email"],

            // Attrs
            [
                "first_name"           => $val["firstname"],
                "last_name"            => $val["lastname"],
                "user_nicename"        => urlencode($val["firstname"]."-".$val["lastname"]),
                "display_name"         => $val["firstname"]." ".$val["lastname"],
                "nickname"             => $val["email"]
            ],

            // Fields
            [
                "phone"            => $val["phone"],
            ]
        );


        if ($user_id instanceof \WP_Error) {
            $data["actions"]["success_message"] = "<p class='error'>Une erreur s'est produite lors de votre inscription :</p>";
            foreach ($user_id->errors as $error_types) {
                foreach ($error_types as $error_msg) {
                    $data["actions"]["success_message"] .= "<p class='error'>$error_msg</p>";
                }
            }
            return $data;
        }


        // ==================================================
        // > SUCCESS MESSAGE
        // ==================================================
        $data["actions"]["success_message"] = $action_settings["success_message"];

        return $data;
    }
}
