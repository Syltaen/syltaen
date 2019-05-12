<?php

namespace Syltaen;

class CoreBLock
{
    const NAME = "Custom Block";
    const SLUG  = "syltaen-block";
    const DESC  = "";

    const KEYWORDS = [];
    const ICON     = "";
    const CATEGORY = "";


    /**
     * Load and process data
     */
    public function __construct()
    {
        $this->data = [];
        $this->load();
    }


    /**
     * Load needed data
     *
     * @return void
     */
    public function load()
    {
        Data::store($this->data, [
            "field_title" => "",
            "field_description" => "Azy koi"
        ]);
    }


    /**
     * Rented a block
     *
     * @return void
     */
    public function render()
    {
        View::render("blocks/monsuperblock", $this->data);
    }


    /**
     * Register fields for a bloc
     *
     * @return void
     */
    public static function fields()
    {
        return [
            [
                "key"   => "field_title",
                "label" => "Title",
                "name"  => "title",
                "type"  => "text",
            ],
            [
                "key"   => "field_description",
                "label" => "Description",
                "name"  => "description",
                "type"  => "textarea",
            ]
        ];

    }


    /**
     * Register a new ACF Block
     *
     * @return void
     */
    public static function register()
    {

        add_action("init", function () {

            // wp_register_script(
            //     "syltaen.tmp",
            //     Files::url("Controllers/Blocs/core/tmp", "block.tmp.js"),
            //     ["wp-blocks", "wp-element"]
            // );

            register_block_type("syltaen/" . static::SLUG, [
                // "editor_script" => "syltaen.tmp",
            ]);

        });
    }
}
