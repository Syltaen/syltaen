<?php

namespace Syltaen;

abstract class Files
{
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
        global $syltaen_paths;

        return $syltaen_paths["folders"][$key];
    }


    /**
     * File path resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function path($key, $filename = "")
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
     * De-register a script by its name
     *
     * @param string $name
     * @return void
     */
    public static function removeScript($name)
    {
        add_action("wp_footer", function () use ($name) {
            wp_dequeue_script($name);
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
        add_action("wp_enqueue_scripts", function () use ($js, $position, $handle) {
            wp_add_inline_script($handle, $js, $position);
        });
    }


    /**
     * Upload a media and create an attachement for it
     *
     * @param string $url
     * @return array The attachement information
     */
    public static function upload($url)
    {
        // Gives us access to the download_url(), wp_handle_sideload() and wp_generate_attachment_metadata()
        require_once(ABSPATH . "wp-admin/includes/file.php");
        require_once(ABSPATH . "wp-admin/includes/image.php");

        // Download file to temp dir
        $temp_file = download_url($url);

        // Check for errors
        if (is_wp_error($temp_file)) return $temp_file;

        // Create a unique file name
        $filename  = wp_unique_filename(wp_upload_dir()["path"], sanitize_file_name(basename($url)));

        // Create a fake file array
        $file = [
            "name"     => $filename,
            "type"     => "image/png",
            "tmp_name" => $temp_file,
            "error"    => 0,
            "size"     => filesize($temp_file),
        ];

        // Move the temporary file into the uploads directory
        $upload = wp_handle_sideload($file, ["test_form" => false, "test_size" => true]);

        // Check for errors
        if (!empty($upload["error"])) return $upload["error"];

        // Generate an attachement
        $upload["id"] = wp_insert_attachment([
            "post_mime_type"    => $upload["type"],
            "post_title"        => $filename,
            "post_content"      => "",
            "post_status"       => "inherit",
        ], wp_upload_dir()["subdir"] . "/" . $filename);

        // Update the attachement's metadata
        $metadata = wp_generate_attachment_metadata($upload["id"], $upload["file"]);

        wp_update_attachment_metadata($upload["id"], $metadata);

        // Return all data
        return $upload;
    }

    /**
     * Autoloader matching PHP-FIG PSR-4 and PSR-0 standarts
     *
     * @param string $classname
     * @return void
     */
    public static function autoload($classname)
    {
        global $syltaen_paths;

        // Not from this namespace
        if (strncmp("Syltaen", $classname, 7) !== 0) return;

        // Remove the namespace "Syltaen"
        $classname = substr($classname, 8);

        // Not indexed in config/paths.php
        if (!array_key_exists($classname, $syltaen_paths["classes"])) return;

        // Try to load the file from the root
        self::load("root", $syltaen_paths["classes"][$classname] . "/" . $classname);
    }

}