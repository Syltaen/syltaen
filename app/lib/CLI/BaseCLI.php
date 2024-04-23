<?php

namespace Syltaen;

use \WP_CLI as WP_CLI;

class BaseCLI
{
    // =============================================================================
    // > FILE CREATION
    // =============================================================================
    /**
     * Create a new class
     *
     * First argument = Class type : Model, Controller
     * Second argument = Name of the class
     * @param  array  $args
     * @return void
     */
    public function make($args)
    {
        if (empty($args[0])) {
            WP_CLI::error("Please provide a class type");
        }

        if (empty($args[1])) {
            WP_CLI::error("Please provide a class name");
        }

        require Files::path("app/lib/CLI/make/CLI_Make.php");

        if (!method_exists(CLI_Make::class, $args[0])) {
            WP_CLI::error("CLI_Make::$args[0] does not exist");
        }

        CLI_Make::{$args[0]}($args[1]);
    }

    // =============================================================================
    // > SETUP
    // =============================================================================
    /**
     * Setup for the theme
     * Usage : wp sytlaen setup
     *
     * @return void
     */
    public function setup()
    {
        // Check ACF
        if (!is_plugin_active("advanced-custom-fields-pro/acf.php")) {
            WP_CLI::warning("Please install ACF Pro from https://www.advancedcustomfields.com/");
        }

        // Set debug mode to true
        static::debug(["true"]);

        // Refresh permalinks
        static::permalinks();

        // Set homepage
        static::sethomepage();
    }

    /**
     * Open all config files in the editor
     *
     * @return void
     */
    public function config()
    {
        $files = ["app/config/_config.php"];

        foreach ($files as $file) {
            exec("code $file");
        }
    }

    /**
     * Set the debug state
     * Usage : wp syltaen debug true
     * @param  array  $args
     * @return void
     */
    public function debug($args)
    {
        if (empty($args)) {
            WP_CLI::error("debug : You must provide a value.");
        }

        // Get wp-config.php file content
        $config_content = file_get_contents(ABSPATH . "wp-config.php");

        // If line exsists : edit it
        $search = "/\n\/?\/?\s*define\(\s*[\'\"]WP_DEBUG[\'\"],\s*[^\s\)]+\s*\);/";
        if (preg_match($search, $config_content)) {
            file_put_contents(
                ABSPATH . "wp-config.php",
                preg_replace($search, "\ndefine(\"WP_DEBUG\", $args[0]);", $config_content)
            );

            // Does not exist : add it
        } else {
            file_put_contents(
                ABSPATH . "wp-config.php",
                preg_replace("/table_prefix[^;]+;/", "$0\n\ndefine(\"WP_DEBUG\", $args[0]);", $config_content)
            );
        }

        WP_CLI::success("Debug state has been set to $args[0]");
    }

    /**
     * Set the permalinks config and refresh them
     * Usage : wp syltaen permalinks
     *
     * @return void
     */
    public function permalinks()
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure("/%postname%/");
        WP_CLI::success("Permalink structure has been set to /%postname%/");

        // Flush rewrite rules
        flush_rewrite_rules();
        WP_CLI::success("Rewrite rules have been flushed");
    }

    /**
     * Set the homepage
     *
     * @return void
     */
    public function sethomepage($args = [])
    {
        $page = (new Pages)->limit(1);

        // Force page ID
        if ($args) {
            $page->is($args[0]);
        }

        $page->update(["post_title" => "Page d'accueil", "post_name" => "accueil", "post_content" => ""]);
        update_option("show_on_front", "page");
        update_option("page_on_front", $page->ID);

        WP_CLI::success("Homepage was set to page #$page->ID");
    }
}