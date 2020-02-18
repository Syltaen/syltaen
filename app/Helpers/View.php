<?php

namespace Syltaen;

class View
{

    const CACHE = true;

    // ==================================================
    // > PUBLIC
    // ==================================================

    /**
     * Render the provided data
     *
     * @param string $filename
     * @param array $data
     * @param boolean $echo
     * @return string|null
     */
    public static function render($filename, $context = false)
    {
        return static::getRenderer()->renderFile(
            static::path($filename),
            static::prepareContext($context)
        );
    }


    /**
     * Display a view
     *
     * @return void
     */
    public static function display($filename, $context = false)
    {
        return static::getRenderer()->displayFile(
            static::path($filename),
            static::prepareContext($context)
        );
    }


    /**
     * Return the HTML view of a WordPress menu
     *
     * @param string|int $menu Either a menu ID or a menu location
     * @param string $classes List of classes to add to the menu
     * @return string HTML output
     */
    public static function menu($menu, $menu_classes = "menu", $item_classes = "", $link_classes = "", $custom_options = [], $custom_processing = false)
    {
        $menu = wp_nav_menu(array_merge([
            "menu"           => $menu,
            "theme_location" => is_string($menu) ? $menu : false,
            "menu_class"     => $menu_classes,
            "container"      => "ul",
            "echo"           => false
        ], $custom_options));

        // Remove all IDs
        $menu = preg_replace("/id=\"[^\"]+\"\s?/", "", $menu);

        // Replace classes
        $menu = preg_replace("/menu-item-/", "{$menu_classes}__item--", $menu);
        $menu = preg_replace("/menu-item/", "{$menu_classes}__item", $menu);
        $menu = preg_replace("/sub-menu/", "{$menu_classes}__sub ", $menu);

        // Add anchor classes
        $menu = preg_replace("/<a/", "<a class=\"{$menu_classes}__link $link_classes\"", $menu);

        // Custom processing
        if ($custom_processing) $menu = $custom_processing($menu);

        // wp_send_json($menu);
        return $menu;
    }

    // ==================================================
    // > PRIVATE
    // ==================================================
    /**
     * Singleton renderer
     *
     * @var \Pug\Pug
     */
    private static $renderer = null;


    /**
     * Get the singleton renderer
     *
     * @return void
     */
    private static function getRenderer()
    {
        if (is_null(static::$renderer)) {
            static::$renderer = new \Pug\Pug([
                "extension"          => ".pug",
                // "expressionLanguage" => "php",

                // Caching
                "cache"         => static::CACHE ? Files::path("app/cache/pug-php/") : false,
                "upToDateCheck" => WP_DEBUG, // Alaws serve cached versions in production

                // Options
                "strict" => true,
            ]);

            static::setOptions();
        }

        return static::$renderer;
    }


    /**
     * Get the full path of a view file
     *
     * @param string $filename
     * @return string
     */
    private static function path($filename)
    {
        $filepath = Files::path("views/" . $filename . ".pug");
        if (!file_exists($filepath)) wp_die("View file not found : $filepath");
        return $filepath;
    }


    /**
     * Get the full path of a view file
     *
     * @param array|bool $context
     * @return string
     */
    private static function prepareContext($context = false)
    {
        if (!$context) return [];

        // Add helper functions
        $context = array_merge(
            $context,
            static::helpers()
        );

        // Add messages
        $context["error_message"]   = Data::currentPage("error_message");
        $context["success_message"] = Data::currentPage("success_message");

        // Empty sections if requested
        if (Data::currentPage("empty_content")) {
            $context = $data["sections"] = [];
        }

        // Apply "content" filter to all the context, triggering shortcodes and the likes
        $context = Data::recursiveFilter($context, "content");

        return $context;
    }



    /**
     * Set options for the rendererd
     *
     * @return void
     */
    private static function setOptions()
    {
        static::$renderer = static::getRenderer();


        static::$renderer;
    }


    /**
     * Add helpers function to the context
     *
     * @return void
     */
    private static function helpers()
    {
        return [

            // Return an image url
            "_img" => function ($image) {

                // Image ID, from WordPress
                if (is_int($image)) {
                    return Data::filter($image, "img:url");
                }

                // Else image in asset
                return Files::url("build/img/" . $image);
            },

            "_imgtag" => function ($id) {
                return Data::filter($id, "img:tag");
            },

            "_tel" => function ($tel) {
                return "tel:" . preg_replace("/[^0-9]/", "", $tel);
            },

            "_mailto" => function ($mail) {
                return "mailto:" . $mail;
            }
        ];
    }
};