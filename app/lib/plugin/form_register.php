<?php

/**
 * Register all custom actions and fields from the theme
 */

namespace Syltaen;

class FormRegisterer
{
    /**
     * The path to the directory in the theme storing actions, fields and templates.
     *
     * @var string
     */
    public $dir;

    /**
     * Hook everything
     */
    public function __construct()
    {
        $this->dir = get_stylesheet_directory()."/app/Forms/";

        add_filter("ninja_forms_register_actions", [$this, "registerActions"]);
        add_filter("ninja_forms_register_fields", [$this, "registerFields"]);
        add_filter("ninja_forms_field_template_file_paths", [$this, "registerTemplates"]);
        add_action("ninja_forms_loaded", [$this, "registerMergeTags"]);

        add_filter("ninja_forms_field_load_settings", [$this, "addCommonSettings"], 10, 3);
    }



    /**
     * Register all custom actions stored in App/Services/Forms/Actions
     *
     * @return void
     */
    public function registerActions($stored_actions)
    {
        return array_merge($stored_actions, $this->getClassesIn("Actions"));
    }

    /**
     * Register all custom fields stored in App/Services/Forms/Fields
     *
     * @return void
     */
    public function registerFields($stored_fields)
    {
        return array_merge($stored_fields, $this->getClassesIn("Fields"));
    }

    /**
     * Register all custom merge tags stored in App/Services/Forms/MergeTags
     *
     * @return void
     */
    public function registerMergeTags()
    {
        return Ninja_Forms()->merge_tags = array_merge(Ninja_Forms()->merge_tags, $this->getClassesIn("MergeTags"));
    }

    /**
     * Register all custom templates stored in App/Services/Forms/templates
     *
     * @return void
     */
    public function registerTemplates($paths)
    {
        return [$this->dir . "templates/"];
    }

    /**
     * Create an instance of each class stored in a specific folder
     *
     * @param string $folder
     * @return array
     */
    private function getClassesIn($folder)
    {
        $classes = [];

        foreach (scandir($this->dir . $folder) as $file) {

            $extension_pos = strpos($file, ".php");
            if ($extension_pos) {
                include $this->dir . "$folder/" . $file;
                $class         = substr($file, 0, $extension_pos);
                $key           = strtolower($class);
                $class         = "Syltaen\\" . $class;
                $classes[$key] = new $class;
            }
        }

        return $classes;
    }


    // ==================================================
    // > FIELDS SETTINGS
    // ==================================================
    /**
     * Add custom settings used for all fields
     *
     * @param array $settings The base settings
     * @param string $type The field type
     * @param string $parent_type The parent field type
     * @return array of settings
     */
    public function addCommonSettings($settings, $type, $parent_type)
    {
        // ========== CONDITIONAL DISPLAY ========== //
        $settings["has_conditional_display"] = [
            "name"  => "has_conditional_display",
            "label" => "Affichage conditionnel",
            "type"  => "toggle",
            "value" => 0,
            "width" => "full",
            "group" => "display",
            "help"  => "Spécifie si le champ doit s'afficher seulement en fonction d'autres champs."
        ];

        $settings["conditional_display"] = [
            "name"  => "conditional_display",
            "label" => __("Valeurs requises", "syltaen").'<a href="#" class="nf-add-new">'.__("Add New")."</a>",
            "type"  => "option-repeater",
            "deps"  => [
                "has_conditional_display" => 1,
            ],
            "columns"        => [
                "label" => [
                    "header"  => "Clef du champ",
                    "default" => null,
                ],
                "value" => [
                    "header"  => "Valeur requise",
                    "default" => null
                ],
                "calc" => [
                    "header"  => "==, !=, ...",
                    "default" => "=="
                ],
                "selected" => [
                    "header"  => "Inclusif",
                    "default" => 0
                ]
            ],
            "width"          => "full",
            "group"          => "display",
            "help"           => "Renseigner un ID de champ et la valeur que le champ doit avoir. Cocher la case inclusif pour que toutes les valeurs cochées soient requises.",
            "use_merge_tags" => true
        ];

        return $settings;
    }
}