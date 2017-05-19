<?php

namespace Syltaen\App\Services;

class Files
{
    /**
     * List of shortcuts for all folders
     */
    const PATHS = [
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
    public static function load(string $folder, $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                require_once(self::path($folder) . $file . ".php");
            }
        } else {
            require_once(self::path($folder) . $files . ".php");
        }
    }


    /**
     * Folder resolution
     *
     * @param string $key
     * @return string
     */
    public static function folder(string $key)
    {
        return self::PATHS[$key];
    }


    /**
     * Path resolution
     *
     * @param string $key
     * @return string
     */
    public static function path(string $key)
    {
        return get_stylesheet_directory() . "/" . self::folder($key) . "/";
    }


    /**
     * Autoloader matching PHP-FIG PSR-4 and PSR-0 standarts
     *
     * @param string $classname
     * @return void
     */
    public static function autoload($classname)
    {
        if (preg_match('/Syltaen/', $classname)) {

            $classname = substr( $classname, 8);

            if (preg_match('/Controller/', $classname)) {
                self::load("controllers", $classname);
            } else {
                self::load("models", $classname);
            }
        }
    }

}

