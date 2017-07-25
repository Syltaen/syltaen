<?php

namespace Syltaen;

class FieldRoles extends FieldAdvancedListSelect
{

    protected $_name      = "fieldroles";

    protected $_section   = "userinfo";

    protected $_icon      = "user";


    public function __construct()
    {
        parent::__construct();
        $this->_nicename = __("Rôles", "syltaen");
    }

    /**
     * Add all commune to the options
     *
     * @param [type] $options
     * @param [type] $settings
     * @return void
     */
    public function filter_options($options, $settings)
    {
        // ========== DEFAULT VALUE WITH MERGE TAGS ========== //
        $default_value = static::getDefault($settings);

        // ========== ADD OPTIONS  ========== //
        foreach (get_editable_roles() as $slug=>$role) {
            $options[] = [
                "label"    => $role["name"],
                "value"    => $slug,
                "selected" => 0
            ];
        }

        // ========== SELECT BASED ON DEFAULT VALUE ========== //
        return parent::filter_options($options, $settings);

    }
}