<?php

namespace Syltaen;

// ==================================================
// > ACF OPTIONS PAGES
// ==================================================
if (function_exists("acf_add_options_page")) {
    // ========== HEADER & FOOTER ========== //
    acf_add_options_page([
        "page_title" => "Header & Footer",
        "menu_title" => "Header & Footer",
        "menu_slug"  => "headerfooter",
        "post_id"    => "headerfooter",
        "capability" => "edit_posts",
        "redirect"   => false,
        "autoload"   => true,
    ]);

    // // ========== OPTIONS ========== //
    // acf_add_options_page([
    //     "page_title" => "Paramètres",
    //     "menu_title" => "Paramètres",
    //     "menu_slug"  => "options",
    //     "post_id"    => "options",
    //     "capability" => "edit_theme_options",
    //     "redirect"   => false,
    //     "autoload"   => true,
    // ]);
}

// ==================================================
// > CACHING SYSTEM
// ==================================================
add_filter("acf/settings/save_json", function ($path) {
    $path = Files::path("app/cache/acf");
    return $path;
});

add_filter("acf/settings/load_json", function ($paths) {
    unset($paths[0]);
    $paths[] = Files::path("app/cache/acf");
    return $paths;
});

// ==================================================
// > GOOGLE MAP KEY
// ==================================================
add_action("acf/init", function () {
    acf_update_setting("google_api_key", "AIzaSyDf2ZP-hkzlrYHYezBIW8JDQYzCnYQGR0o"); // Should only be used in admin
});