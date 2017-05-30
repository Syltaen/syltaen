<?php

namespace Syltaen\App\Services\Forms\Actions;

use Syltaen\Models\Posts\Applications;

if (!defined("ABSPATH") || !class_exists("NF_Abstracts_Action")) exit;


final class SaveApplication extends \NF_Abstracts_Action
{
    /**
     * @var string
     */
    protected $_name  = "saveapplication";

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

        $this->_nicename = __("Job application saving", "syltaen");

        $this->_settings["save_application_success"] = [
            "name"           => "save_application_success",
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
        // > SAVE APPLICATION
        // ==================================================
        $storeFolder = wp_upload_dir();
	    $storeFolder = $storeFolder["baseurl"] . "/ninja-forms/";

        Applications::add($val["firstname"] . " " . $val["lastname"], "", [
            "job_id"    => $val["job_id"],
            "firstname" => $val["firstname"],
            "lastname"  => $val["lastname"],
            "email"     => $val["email"],
            "phone"     => $val["phone"],
            "cv"        => $storeFolder . "cv/" . $val["cv"]
        ], "pending");

        // ==================================================
        // > SUCCESS MESSAGE
        // ==================================================
        if (isset($action_settings["save_application_success"])) {
            $data["actions"]["success_message"] = do_shortcode( $action_settings["save_application_success"] );
        }

        return $data;
    }
}
