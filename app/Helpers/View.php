<?php

namespace Syltaen;

class View
{
    const CACHE = true; //true; //!WP_DEBUG;

    // ==================================================
    // > PUBLIC
    // ==================================================

    /**
     * Render the provided data
     *
     * @param  string        $filename
     * @param  array         $data
     * @param  boolean       $echo
     * @return string|null
     */
    public static function render($filename, $context = false, $is_main_context = false)
    {
        return apply_filters("syltaen_render", static::getRenderer()->renderFile(
            static::path($filename),
            (array) ($is_main_context ? static::prepareMainContext($context) : $context)
        ));
    }

    /**
     * Display a view
     *
     * @return void
     */
    public static function display($filename, $context = false, $is_main_context = false)
    {
        echo static::render($filename, $context, $is_main_context);
    }

    /**
     * Light render of a pug string
     *
     * @return string
     */
    public static function parsePug($pug, $context = [])
    {
        return static::getRenderer()->render($pug, $context);
    }

    /**
     * Return the HTML view of a WordPress menu
     *
     * @param  string|int $menu    Either a menu ID or a menu location
     * @param  string     $classes List of classes to add to the menu
     * @return string     HTML output
     */
    public static function menu($menu, $menu_classes = "menu", $item_classes = "", $link_classes = "", $custom_options = [], $custom_processing = false)
    {
        $menu = wp_nav_menu(array_merge([
            "menu"           => $menu,
            "theme_location" => is_string($menu) ? $menu : false,
            "menu_class"     => $menu_classes,
            "container"      => "ul",
            "echo"           => false,
        ], $custom_options));

        // Remove all IDs
        // $menu = preg_replace("/id=\"[^\"]+\"\s?/", "", $menu);

        // Replace classes
        $menu = preg_replace("/menu-item-/", "{$menu_classes}__item--", $menu);
        $menu = preg_replace("/menu-item/", "{$menu_classes}__item", $menu);
        $menu = preg_replace("/sub-menu/", "{$menu_classes}__sub", $menu);

        // Add anchor classes
        $menu = preg_replace("/<a(?! class) /", "<a class=\"{$menu_classes}__link $link_classes\"", $menu);

        // Custom processing
        if ($custom_processing) {
            $menu = $custom_processing($menu);
        }

        return do_shortcode($menu);
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
                "expressionLanguage" => "php",

                // Caching
                "cache"              => static::CACHE ? Files::path("app/cache/pug-php/") : false,
                "upToDateCheck"      => WP_DEBUG, // Alaws serve cached versions in production

                // Options
                "strict"             => true,
                "basedir"            => Files::path("/"),

                "debug"              => WP_DEBUG,
            ]);
        }

        return static::$renderer;
    }

    /**
     * Get the full path of a view file
     *
     * @param  string   $filename
     * @return string
     */
    private static function path($filename)
    {
        $filepath = Files::path("views/" . $filename . ".pug");
        if (!file_exists($filepath)) {
            wp_die("View file not found : $filepath");
        }

        return $filepath;
    }

    /**
     * Get the full path of a view file
     *
     * @param  array|bool $context
     * @return string
     */
    private static function prepareMainContext($context = false)
    {
        // Make sure every array in the context is a set
        $context = new RecursiveSet($context, true);

        // Add messages
        $context->error_message   = Data::currentPage("error_message");
        $context->success_message = Data::currentPage("success_message");

        // Empty sections if requested
        if (Data::currentPage("empty_content")) {
            $context->sections = [];
        }

        // Add custom data from hooks
        $context = apply_filters("syltaen_render_context", $context);

        // Add helper functions
        $context = $context->merge(static::helpers());

        // Apply global filters
        return $context;
    }

    /**
     * Add helpers function to the context
     *
     * @return void
     */
    private static function helpers()
    {
        // return $ambiance ?: Files::url("build/img/thumb_placeholder.png");

        return [

            // Return an image url
            "_img"    => function ($image, $size = "full") {
                // Image ID, from WordPress
                if (is_int($image)) {
                    return wp_get_attachment_image_url($image, $size);
                }

                // Else image in asset
                return Files::url("build/img/" . $image);
            },

            "_imgtag" => function ($id, $size = "full") {
                return wp_get_attachment_image($id, $size);
            },

            "_tel"    => function ($tel) {
                return "tel:" . preg_replace("/[^0-9]/", "", $tel);
            },

            "_mailto" => function ($mail) {
                return "mailto:" . $mail;
            },
        ];
    }
};