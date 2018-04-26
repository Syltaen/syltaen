<?php

namespace Syltaen;

abstract class Files
{
    // ==================================================
    // > PATHS & LOADING
    // ==================================================
    /**
     * Load one or several files by providing a folder shortcut and a list of filenames
     *
     * @param string $folder
     * @param array|string $files
     * @return void
     */
    public static function import($folder, $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                require_once(self::path($folder, "$file.php"));
            }
        } else {
            return require_once(self::path($folder, "$files.php"));
        }
    }

    /**
     * File path resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function path($folder = "", $filename = "")
    {
        return get_stylesheet_directory() . "/" . $folder . "/" . $filename;
    }

    /**
     * File url resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function url($folder = "", $filename = "")
    {
        return get_template_directory_uri() . "/" . $folder . "/" . $filename;
    }

    /**
     * Return the time the file was last modified
     *
     * @param string $key
     * @param string $file
     * @return int : number of ms
     */
    public static function time($folder, $file)
    {
        return filemtime(self::path($folder, $file));
    }

    /**
     * Find a file in one off the provided folders
     *
     * @param string $file The name of the file
     * @param array $folders A list of folder's paths (from the theme root)
     * @return string The file path
     */
    public static function findIn($file, $folders, $depth = 2)
    {
        // Create the folder pattern
        $folders = array_map(function ($folder) {
            return self::path($folder);
        }, $folders);
        $folder_pattern = implode(",", $folders);

        // Create the depth pattern
        for ($depth_pattern_folder = $depth_pattern = ""; $depth > 0; $depth--) {
            $depth_pattern_folder .= "*/";
            $depth_pattern        .= ",$depth_pattern_folder";
        }

        // Glob using the two patterns
        $results = glob("{" . $folder_pattern . "}" . "{" . $depth_pattern . "}" . $file, GLOB_BRACE);

        if (empty($results)) return false;

        return $results[0];
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
                Files::url("build/js", $file),
                $requirements,
                Files::time("build/js", $file),
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
                Files::url("build/css", $file),
                $requirements,
                Files::time("build/css", $file)
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

    // ==================================================
    // > UPLOADING & MEDIAS
    // ==================================================
    /**
     * Create attachements for an array of files
     *
     * @param array $files
     * @param integer $parent_post_id
     * @return array of files
     */
    private static function generateAttachement($files, $parent_post_id = 0)
    {
        require_once(ABSPATH . "wp-admin/includes/image.php");

        return array_map(function ($file) use ($parent_post_id) {

            // Generate an attachement
            $file["id"] = wp_insert_attachment([
                "post_mime_type"    => $file["type"],
                "post_title"        => basename($file["file"]),
                "post_content"      => "",
                "post_status"       => "inherit",
            ], $file["file"], $parent_post_id);

            // Update the attachement's metadata
            $metadata = wp_generate_attachment_metadata($file["id"], $file["file"]);
            wp_update_attachment_metadata($file["id"], $metadata);

            return $file;

        }, (array) $files);
    }


    /**
     * Upload a list of files stored in a $_FILES format
     *
     * @param array $files
     * @param string $folder A custom folder to store the files. Default : yyyy/mm
     * @return array of files
     */
    public static function upload($files, $folder = null, $generateAttachement = false, $parent_post_id = 0)
    {
        require_once(ABSPATH . "wp-admin/includes/file.php");

        // $basedir        = wp_upload_dir()["basedir"];
        $uploaded_files = [];

        // Upload the files in the right folder
        foreach ((array) $files as $file) {

            if ($file["error"]) continue;

            $uploaded_file = wp_handle_upload($file, [
                "test_form" => false
            ], false);

            $uploaded_files[] = $uploaded_file;
        }

        // Create an attachement if requested
        if ($generateAttachement) {
            return static::generateAttachement($uploaded_files, $parent_post_id);
        }

        return $uploaded_files;
    }

    /**
     * Upload a media and create an attachement for it
     *
     * @param string $url
     * @return array The attachement information
     */
    public static function uploadFromUrl($url, $folder = null, $generateAttachement = false, $parent_post_id = 0)
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

        // Create an attachement if requested
        if ($generateAttachement) {
            $upload = static::generateAttachement([$upload], $parent_post_id)[0];
        }

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
        // Not from this namespace
        if (strncmp("Syltaen", $classname, 7) !== 0) return;

        // Remove the namespace "Syltaen"
        $classname = substr($classname, 8);

        // Find the file in one of the classes folders
        if ($found = self::findIn("{$classname}.php", ["app/lib", "app/Helpers", "Controllers", "Models", "app/Forms"])) {
            require_once $found;
        }
    }
}