<?php

namespace Syltaen;

add_action("init", function() {

    /**
     * Register all custom post types and taxonomies found in these folders.
     * Prepend the filename with . to prevent it from being registered
     */

    $registration_folders = [
        "Models/Taxonomies",
        "Models/Posts"
    ];

    foreach ($registration_folders as $folder) {
        foreach (Files::in($folder) as $file) {
            Files::import("$folder/{$file}");
            $class = "Syltaen\\" . str_replace(".php", "", $file);
            $class::register();
        }
    }

});