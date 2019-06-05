<?php

namespace Syltaen;

class ACFBLock
{
    const NAME = "Custom ACF Block";
    const SLUG  = "custom-acf-block";
    const DESC  = "";

    const KEYWORDS = [];
    const ICON     = "";
    const CATEGORY = "";

    public $view = "block";

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
        // Register the block
        acf_register_block([
            "name"            => static::SLUG,
            "title"           => static::NAME,
            "description"     => static::DESC,
            "render_callback" => function () {
                return (new static)->render();
            }
        ]);

        // Register its fields
        acf_add_local_field_group([
            "key"    => static::SLUG . "-fields",
            "title"  => "Bloc : " . static::NAME,
            "fields" => static::fields(),
            "location" => [
                [
                    [
                        "param"    => "block",
                        "operator" => "==",
                        "value"    => "acf/".static::SLUG,
                    ],
                ],
            ]
        ]);
    }
}