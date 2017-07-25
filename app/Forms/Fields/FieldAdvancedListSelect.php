<?php

namespace Syltaen;

class FieldAdvancedListSelect extends \NF_Fields_ListSelect
{
    protected $_name = "fieldadvancedlistselect";

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("Choisir (avancé)", "syltaen");

        $this->_settings["default"] = [
            "name"           => "default",
            "label"          => __("Default Value", "ninja-forms"),
            "type"           => "textbox",
            "width"          => "full",
            "group"          => "advanced",
            "value"          => "",
            "use_merge_tags" => true
        ];

        add_filter("ninja_forms_render_options_". $this->_name, [$this, "filter_options"], 10, 2);
    }

    protected static function applyMergeTag($string)
    {
        return preg_replace_callback('/{([^:{}]*):([^:{}]*)}/', function ($parts) {
            switch ($parts[1]) {
                case "website_user":
                    $mt = new MergeTagsUser;
                    break;
                default:
                    return $parts[0];
            }


            if ($mt->init()) {
                return $mt->{$parts[2]}();
            }

            return "";
        }, $string);
    }

    /**
     * Apply merge tags to a value
     *
     * @param [type] $settings
     * @return void
     */
    protected static function getDefault($settings)
    {
        $default_value = isset($settings["default"]) && $settings["default"] ? $settings["default"] : "";
        $default_value = static::applyMergeTag($default_value);

        return $default_value;
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

        // ========== POPULATE OPTIONS ========== //
        foreach ($options as &$option) {
            if ($option["value"] == $default_value) $option["selected"] = true;
        }

        return $options;
    }

}