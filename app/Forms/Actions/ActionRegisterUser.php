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

        $this->_nicename = __("Inscription utilisateur - Intranet & Application", "syltaen");
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
        // > THE SELECTED PROJECT
        // ==================================================
        $project = (new Projects)->is($val["projet"])->getOne();
        if (!$project) {
            $data["actions"]["error_message"] = "Ce projet n'a pas été trouvé";
            return $data;
        }

        // ==================================================
        // > REGISTER USER
        // ==================================================
        $user_id = Users::add($val["login"], $val["password"], $val["login"],

            // Attrs
            [
                "first_name"           => $val["firstname"],
                "last_name"            => $val["lastname"],
                "user_nicename"        => urlencode($val["firstname"]."-".$val["lastname"]),
                "display_name"         => $val["firstname"]." ".$val["lastname"],
                "nickname"             => $val["login"]
            ],

            // Fields
            [
                "intranet_project" => $val["projet"],
                "phone"            => $val["phone"],
                "commune"          => $val["commune"],
                "fonction"         => $val["fonction"] == "Autre" ? $val["other"] : $val["fonction"],
                "service"          => $val["service"],
                "user_key"         => sha1(microtime(true).mt_rand(10000,90000)),
                "app_password"     => "{MD5}".base64_encode(md5($val["password"], true)),
                "state"            => "to_validate",
            ],

            // Roles
            $project->user_role
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
        // > MAILS
        // ==================================================

        // ========== USER ========== //
        if ($project->send_mail_registration) {
            Mail::send($val["login"], $project->mail_registration_subject, $project->mail_registration_body);
        }

        // ========== ADMINS ========== //
        if ($project->send_mail_registration_admin) {
            Mail::send(
                $project->mail_registration_admin_to,
                $project->mail_registration_admin_subject,
                str_replace(
                    "[profile_link]",
                    site_url("profil?user=".$user_ID),
                    $project->mail_registration_admin_body
                )
            );
        }

        // ==================================================
        // > SUCCESS MESSAGE
        // ==================================================
        if (isset($action_settings["registration_user_success"])) {
            $data["actions"]["success_message"] = do_shortcode($project->success_message);
        }

        return $data;
    }
}
