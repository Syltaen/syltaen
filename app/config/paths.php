<?php

/**
 * An index of all classes and files that would need to be loaded.
 * All to use a fast and custom directory structure for classes autoload
 * without the need to use complex namespaces.
 */

return [

// ==================================================
// > CLASSES
// ==================================================
    "classes" => [

        // ========== MODELS ========== //
        "Model"                  => "Models",

        // Posts
        "Posts"                  => "Models/Posts",
        "News"                   => "Models/Posts",
        "Pages"                  => "Models/Posts",

        // Taxnomies
        "Taxonomy"               => "Models/Taxonomies",
        "NewsTaxonomy"           => "Models/Taxonomies",

        // Users
        "Users"                  => "Models/Users",

        // ========== CONTROLLERS ========== //
        "Controller"              => "Controllers",

        "ApiController"           => "Controllers",

        "PageController"          => "Controllers/Pages",
        "HomeController"          => "Controllers/Pages",
        "ContentPageController"   => "Controllers/Pages",
        "SingleController"        => "Controllers/Pages",
        "SpecialPageController"   => "Controllers/Pages",

        "SectionsController"      => "Controllers/Parts",
        "ArchiveController"       => "Controllers/Parts",

        // ========== HELPERS ========== //
        "Ajax"       => "app/Helpers",
        "Cache"      => "app/Helpers",
        "Data"       => "app/Helpers",
        "Files"      => "app/Helpers",
        "Mail"       => "app/Helpers",
        "Pagination" => "app/Helpers",
        "Request"    => "app/Helpers",
        "Route"      => "app/Helpers"
    ],

// ==================================================
// > FOLDERS
// ==================================================
    "folders" => [
        "root"  => "/",

        "app"   => "app",
            "cache"      => "app/cache",
                "cache-acf" => "app/cache/acf",
                "cache-pug" => "app/cache/pug-php",
            "config"     => "app/config",
            "vendors"    => "app/vendors",
            "forms"      => "app/Forms",
            "helpers"    => "app/Helpers",
            "hooks"      => "app/hooks",
                "actions"    => "app/hooks/actions",
                "ajax"       => "app/hooks/ajax",
                "filters"    => "app/hooks/filters",


        "controllers" => "Controllers",

        "models"      => "Models",

        "build"      => "build",
            "css"         => "build/css",
            "fonts"       => "build/fonts",
            "img"         => "build/img",
            "js"          => "build/js",

        "scripts"     => "scripts",
        "styles"      => "styles",

        "views"       => "views",
    ]
];