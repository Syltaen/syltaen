<?php

namespace Syltaen;

class FieldAdvancedMultiSelect extends FieldAdvancedListSelect
{
    /**
     * @var string
     */
    protected $_name = "fieldadvancedmultiselect";

    /**
     * @var string
     */
    protected $_templates = "selectmultiple";

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = "Sélection multiple (avancé)";

    }

    /**
     * Add all commune to the options
     *
     * @param  [type] $options
     * @param  [type] $settings
     * @return void
     */
    public function filter_options($options, $settings)
    {
        // ========== DEFAULT VALUE WITH MERGE TAGS ========== //
        $default_value = static::getDefault($settings);

        // ========== POPULATE OPTIONS ========== //
        foreach ($options as &$option) {
            if ($option["value"] && strpos($default_value, $option["value"] . ";") !== false) {
                $option["selected"] = 1;
            }
        }

        return $options;
    }
}