<?php

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
add_image_size("full-width", 9999, 9999);
// add_filter("image_size_names_choose", function ($sizes) {
//     return array_merge($sizes, ["full-width" => __( "Pleine largueur" )]);
// });

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
if (get_role("contributor")) {
    remove_role("contributor");
}

if (get_role("author")) {
    remove_role("author");
}

if (get_role("subscriber")) {
    remove_role("subscriber");
}

// ==================================================
// > DISABLE SMART TEXTS
// ==================================================
add_filter("run_wptexturize", "__return_false");