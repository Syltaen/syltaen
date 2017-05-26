<?php

namespace Syltaen\App\Services;

class Files
{
    /**
     * List of shortcuts for all folders
     */
    const PATHS = [
        "root"  => "/",
        "app"   => "App",
            "config"     => "App/config",
            "vendors"    => "App/vendors",
            "generator"  => "App/Generators",
            "hooks"      => "App/hooks",
                "actions" => "App/hooks/actions",
                "ajax"    => "App/hooks/ajax",
                "filters" => "App/hooks/filters",
            "services"   => "App/Services",
        "controllers" => "Controllers",
        "models"      => "Models",
        "scripts"     => "scripts",
        "src"         => "src",
            "js"  => "src/js",
            "css" => "src/css",
            "img" => "scr/img",
        "styles"      => "styles",
        "views"       => "views",
    ];

    /**
     * Load one or several files by providing a folder shortcut and a list of filenames
     *
     * @param string $folder
     * @param array|string $files
     * @return void
     */
    public static function load($folder, $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                require_once(self::path($folder, "$file.php"));
            }
        } else {
            require_once(self::path($folder, "$files.php"));
        }
    }


    /**
     * Folder key to folder name
     *
     * @param string $key
     * @return string
     */
    public static function folder($key)
    {
        return self::PATHS[$key];
    }


    /**
     * File path resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function path($key, $filename)
    {
        return get_stylesheet_directory() . "/" . self::folder($key) . "/" . $filename;
    }

    /**
     * File url resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function url($key, $filename)
    {
        return get_template_directory_uri() . "/" . self::folder($key) . "/" . $filename;
    }

    /**
     * Return the time the file was last modified
     *
     * @param string $key
     * @param string $file
     * @return int : number of ms
     */
    public static function time($key, $file)
    {
        return filemtime(self::path($key, $file));
    }

    /**
     * Enqueue a script stored in the js folder
     *
     * @param string $file
     * @param array $requirements
     * @param string $action
     * @return void
     */
    public static function addScript($file, $requirements = [], $action = "wp_enqueue_scripts")
    {
        add_action($action, function () use ($file, $requirements ){
            wp_enqueue_script(
                $file,
                Files::url("js", $file),
                $requirements,
                Files::time("js", $file),
                true
            );
        });
    }

    /**
     * Enqueue a style stored in the css folder
     *
     * @param string $file
     * @param array $requirements
     * @param string $action
     * @return void
     */
    public static function addStyle($file, $requirements = [], $action = "wp_enqueue_scripts")
    {
        add_action($action, function () use ($file, $requirements) {
            wp_enqueue_style(
                $file,
                Files::url("css", $file),
                $requirements,
                Files::time("css", $file)
            );
        });
    }

    /**
     * Write custom js with php
     *
     * @param string $js the JS code to be written
     * @param string $position "before" or "after"
     * @param string $handle script name used by the $position argument
     * @return void
     */
    public static function addInlineScript($js, $position, $handle)
    {
        add_action( "wp_enqueue_scripts", function () use ($js, $position, $handle) {
            wp_add_inline_script($handle, $js, $position);
        });
    }

    /**
     * Autoloader matching PHP-FIG PSR-4 and PSR-0 standarts
     *
     * @param string $classname
     * @return void
     */
    public static function autoload($classname)
    {
        // check if class is in the app namespace
        if (strncmp('Syltaen', $classname, 7) !== 0) return;
        // remove the app prefix
        $classname = substr($classname, 8);
        // require the full file from root folder
        self::load('root', str_replace('\\', '/', $classname));
    }

}