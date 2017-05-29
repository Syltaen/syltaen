<?php

use Syltaen\Models\Users\Users;

// ==================================================
// > THEME SUPPORTS
// ==================================================
add_theme_support("automatic-feed-links");
add_theme_support("title-tag");
add_theme_support("html5", [
    "search-form",
    // "comment-form",
    // "comment-list",
    // "gallery",
    // "caption"
]);
add_theme_support("post-thumbnails");

// ==================================================
// > IMAGE SIZES
// ==================================================
// see https://developer.wordpress.org/reference/functions/add_image_size/
add_image_size("full-width", 9999, 9999);
update_option("medium_crop", 1);

// ==================================================
// > UPLOADS
// ==================================================
add_filter("upload_mimes", function ($existing_mimes = []) {
    $existing_mimes["eps"] = "application/postscript";
    $existing_mimes["zip"] = "application/zip";
    $existing_mimes["ai"]  = "application/postscript";
    return $existing_mimes;
});

// ==================================================
// > ROLES
// ==================================================
Users::unregisterRoles([
    "contributor",
    "author",
    "subscriber"
]);

// ==================================================
// > DISABLE SMART TEXTS
// ==================================================
add_filter("run_wptexturize", "__return_false");


// ==================================================
// > FIXES
// ==================================================
remove_action("shutdown", "wp_ob_end_flush_all", 1 );