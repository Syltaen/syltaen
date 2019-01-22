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
    public static function import($folders = [""], $files = [""])
    {
        $folders = (array) $folders;
        $files   = (array) $files;
        $list    = [];

        // Create an import list
        foreach ($folders as $folder) {
            foreach ($files as $file) {
                $list[] = trim($folder ."/" . $file, "/");
            }
        }

        foreach ($list as $item) {
            // Is a file
            if (strpos($item, ".")) {
                require_once(self::path($item));
                continue;
            }

            // Is a folder
            foreach (self::in($item, ".php") as $file) {
                require_once(self::path($item . "/" . $file));
            }
        }
    }



    /**
     * File path resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function path($path_from_root = "")
    {
        return str_replace("\\", "/", get_stylesheet_directory() . "/" . $path_from_root);
    }

    /**
     * File url resolution
     *
     * @param string $key
     * @param string $filename
     * @return string
     */
    public static function url($path_from_root = "")
    {
        return get_template_directory_uri() . "/" . $path_from_root;
    }

    /**
     * Return the time the file was last modified
     *
     * @param string $key
     * @param string $file
     * @return int : number of ms
     */
    public static function time($file)
    {
        return filemtime(self::path($file));
    }


    // ==================================================
    // > ENQUEUING
    // ==================================================
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
                self::url("build/js/{$file}"),
                $requirements,
                self::time("build/js/{$file}"),
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
                self::url("build/css/{$file}"),
                $requirements,
                self::time("build/css/{$file}")
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
        if ($found = self::findIn("{$classname}.php", [
            "app/lib",
            "app/Helpers",
            "Controllers",
            "Controllers/processors",
            "Models",
            "app/Forms"
        ])) {
            require_once $found;
        }
    }

    // ==================================================
    // > SCANNING
    // ==================================================
    /**
     * Find a file in one off the provided folders
     *
     * @param string $file The name of the file
     * @param array $folders A list of folder's paths (from the theme root)
     * @param bool $returnAll Return all matches instead of only the first one
     * @return string The file path
     */
    public static function findIn($file, $folders, $depth = 2, $returnAll = false)
    {
        // Create the folder pattern
        $folders = array_map(function ($folder) {
            return self::path($folder);
        }, $folders);

        // Create the depth pattern
        for ($depth_pattern_folder = $depth_pattern = ""; $depth > 0; $depth--) {
            $depth_pattern_folder .= "*/";
            $depth_pattern        .= ",$depth_pattern_folder";
        }

        $results = [];

        // Search in each folder, one by one and merge to $result
        foreach ($folders as $folder) {
            $results = array_merge($results, glob($folder . "{" . $depth_pattern . "}" . $file, GLOB_BRACE));
        }

        if (empty($results)) return false;

        return $returnAll ? $results : $results[0];
    }

    /**
     * Return a list of files found in a specific folder
     *
     * @param string $folder
     * @return array
     */
    public static function in($folder, $match = false, $show_hidden = false)
    {
        $files  = [];
        foreach (scandir(self::path($folder)) as $file) {

            // Does not list hidden files or navigation
            if (!$show_hidden && $file[0] == ".") continue;

            // Has to match
            if ($match && strpos($file, $match) === false) continue;

            $files[] = $file;
        }

        return $files;
    }


    /**
     * Scan all pug files for translations and add them in app/lang/view-strings.php
     *
     * @return void
     */
    public static function scanPugTranslations()
    {
        $pugs    = static::findIn("*.pug", ["views"], 5, true);
        $matches = [];

        // Scan the files for stings
        foreach ($pugs as $pug) {
            preg_match_all('/_.\(.+(?!\"\).)\)/', file_get_contents($pug), $match);

            if (!empty($match[0])) $matches[] = "\n\n//> " . basename($pug);
            $matches = array_merge($matches, $match[0]);
        }

        // Get view-strings.php
        $content = file_get_contents(self::path("app/lang/view-strings.php"));
        $content = explode("\n", $content);
        // Keep only the header
        $content = array_slice($content, 0, 7);
        // Add each line
        foreach ($matches as $line) $content[] = $line . ";";
        // Re-write the content into the file
        file_put_contents(self::path("app/lang/view-strings.php"), implode("\n", $content));
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
            $file["ID"] = wp_insert_attachment([
                "post_mime_type"    => $file["type"],
                "post_title"        => basename($file["file"]),
                "post_content"      => "",
                "post_status"       => "inherit",
            ], $file["file"], $parent_post_id);

            // Update the attachement's metadata
            $metadata = wp_generate_attachment_metadata($file["ID"], $file["file"]);
            wp_update_attachment_metadata($file["ID"], $metadata);

            return $file;

        }, (array) $files);
    }


    /**
     * Create an array of files item, separating multi-files into separate entries
     *
     * @param array $files Array of files
     * @return array
     */
    public static function flattenFilesArray($files)
    {
        $unclean = (array) $files;
        $files   = [];

        // Transform multiple-files array into separates files
        foreach ($unclean as $name=>$file) {

            // Not a multipe-files array
            if (!is_array($file["name"])) {
                $files[$name] = $file;
                continue;
            }

            // Multiple-files array, flatten it
            foreach ($file["name"] as $index=>$n) {
                $newFile = [];
                foreach ($file as $attr=>$value) $newFile[$attr] = $value[$index];
                $files[$name."_".$index] = $newFile;
            }
        }

        return $files;
    }
    /**
     * Upload a list of files stored in a $_FILES format
     *
     * @param array $files
     * @param string $folder A custom folder to store the files. Default : yyyy/mm
     * @return array of files
     */
    public static function upload($files, $generateAttachement = false, $parent_post_id = 0)
    {
        if (empty($files)) return [];

        require_once(ABSPATH . "wp-admin/includes/file.php");
        $uploaded_files = [];

        // Upload the files
        foreach (static::flattenFilesArray($files) as $file) {

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

    // ==================================================
    // > TOOLS
    // ==================================================
    /**
     * Get a list of file extensions
     * @see https://fileinfo.com/filetypes/common
     * @see https://codex.wordpress.org/Uploading_Files#About_Uploading_Files_on_Dashboard
     * @param array $types
     * @param string $format
     * @param bool $safe Only include extensions that are not filtered by WordPress
     * @return mixed
     */
    public static function extensions($types = [], $format = "string", $safe = true)
    {
        // Lists of extensions
        $extensions = [
            "images"     => [".jpg",".jpeg",".gif",".png",".ico", ".bmp", ".dds", ".psd", ".pspimage", ".tga", ".thm", ".tif", ".tiff", ".yuv", ".ai", ".eps", ".ps", ".svg"],
            "text"       => [".doc", ".docx", ".log", ".msg", ".odt", ".pages", ".rtf", ".tex", ".txt", ".wpd", ".wps"],
            "data"       => [".csv", ".dat", ".ged", ".key", ".keychain", ".sdf", ".tar", ".tax2016", ".tax2017", ".vcf", ".xml", ".pps", ".ppt", ".pptx", ".xlr", ".xls", ".xlsx"],
            "audio"      => [".aif", ".iff", ".m3u", ".m4a", ".mid", ".mp3", ".mpa", ".wav", ".wma"],
            "video"      => [".3g2", ".3gp", ".asf", ".avi", ".flv", ".m4v", ".mov", ".mp4", ".mpg", ".rm", ".srt", ".swf", ".vob", ".wmv"],
            "compressed" => [".7z", ".cbr", ".deb", ".gz", ".pkg", ".rar", ".rpm", ".sitx", ".tar.gz", ".zip", ".zipx"],
        ];

        // Filter by types
        if ($types) {
            $extensions = array_filter($extensions, function ($key) use ($types) {
                return in_array($key, $types);
            }, ARRAY_FILTER_USE_KEY);
        }

        // Merge all
        $list = [];
        foreach ($extensions as $ext) $list = array_merge($list, $ext);

        // Filter unallowed by WordPress
        if ($safe) {
            $allowed = get_allowed_mime_types();
            $list = array_filter($list, function ($extension) use ($allowed) {
                foreach ($allowed as $test_ext=>$test_mime) {
                    if (preg_match("!\.(".$test_ext.")$!i", $extension, $match)) return true;
                }
                return false;
            });
        }

        // Return formated
        switch ($format) {
            case "array":
                return $list;
            case "string":
            default:
                return implode(", ", $list);
        }
    }
}