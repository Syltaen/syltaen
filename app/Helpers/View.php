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
        // Register defual default context and render hooks
        static::registerHooks();

        if ($is_main_context) {
            apply_filters("syltaen_render_main_context", $context);
        }

        return apply_filters("syltaen_render",
            static::getRenderer()->renderFile(
                static::path($filename),
                (array) apply_filters("syltaen_render_context", $context)
            )
        );
    }

    /**
     * Register defual default context and render hooks
     *
     * @return void
     */
    public static function registerHooks()
    {
        // Add stuff for the main context
        add_filter("syltaen_render_main_context", "\Syltaen\View::prepareMainContext");

        // Add helpers
        add_filter("syltaen_render_context", "\Syltaen\View::addHelpers");
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

    /**
     * Render the result of a mixin, with specific arguments
     *
     * @param  string   $path
     * @param  array    $args
     * @return string
     */
    public static function mixin($path, $args)
    {
        // Include the mixin
        $include = "include /views/mixins/{$path}";
        // Generate mixin call
        $mixin = explode("/_", $path);
        $mixin = end($mixin);
        $mixin = "+$mixin(" . implode(", ", array_map(function ($var) {return "\$$var";}, array_keys($args))) . ")";
        // Render both with arguments
        return View::parsePug(implode("\n", [$include, $mixin]), $args);
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
     * @return object
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

    // =============================================================================
    // > HOOKED METHODS
    // =============================================================================
    /**
     * Get the full path of a view file
     *
     * @param  array|bool $context
     * @return string
     */
    public static function prepareMainContext($context = false)
    {
        // Make sure every array in the context is a set
        $context = new RecursiveSet($context, true);

        // Add messages
        $context["error_message"]   = Data::currentPage("error_message");
        $context["success_message"] = Data::currentPage("success_message");

        // Empty sections if requested
        if (Data::currentPage("empty_content")) {
            $context->sections = [];
        }

        // Apply global filters
        return $context;
    }

    /**
     * Add helpers function to the context
     *
     * @return void
     */
    public static function addHelpers($context)
    {
        $_img = function ($image, $size = "full") {
            // Image ID, from WordPress
            if (is_int($image)) {
                return wp_get_attachment_image_url($image, $size);
            }

            // Else image in asset
            return Files::url("build/img/" . $image);
        };

        return set($context)->merge([

            // Return an image url
            "_img"       => $_img,

            // Return an image
            "_bg"        => function ($image, $size = "full") use ($_img) {
                return "background-image: url(" . $_img($image, $size) . ");";
            },

            // Image tag
            "_imgtag"    => function ($id, $size = "full") {
                return wp_get_attachment_image($id, $size);
            },

            // Phone number link
            "_tel"       => function ($tel) {
                return "tel:" . preg_replace("/[^0-9]/", "", $tel);
            },

            // Mailto link
            "_mailto"    => function ($mail) {
                return "mailto:" . $mail;
            },

            // Localized date format
            "_date"      => function ($format, $date) {
                return date_i18n($format, strtotime($date));
            },

            // Class modifier(s)
            "_modifiers" => function ($class, $modifiers, $include_class = false) {
                $modifiers = array_map(function ($modifier) use ($class) {
                    return "{$class}--{$modifier}";
                }, (array) $modifiers);
                return $include_class ? array_merge([$class], $modifiers) : $modifiers;
            },

            // Option
            "_option"    => function ($key, $fallback = "") {
                return Data::option($key, $fallback);
            },

            // Get a custom route
            "_route"     => function ($key) {
                return Route::getCustom($key);
            },

            // FA unicode
            "_fa"        => function ($icon) {
                return Text::fa($icon);
            },

            // Sets' methods
            "_set"       => function ($array) {
                return set($array);
            },
        ]);
    }
};