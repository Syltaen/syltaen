<?php

namespace Syltaen;

// =============================================================================
// > FRONT
// =============================================================================

// ==================================================
// > JS
// ==================================================
Files::removeScript("jquery");

Files::addScript("bundle.js");

Files::addInlineScript(
    "var ajaxurl = '".admin_url("admin-ajax.php")."';".
    "window.location.site = '".site_url("/")."';",
    "before",
    "bundle.js"
);

// GOOGLE MAPS
// add_action("wp_enqueue_scripts", function () {
//     wp_enqueue_script("google.maps", "https://maps.googleapis.com/maps/api/js?key=AIzaSyBqGY0yfAyCACo3JUJbdgppD2aYcgV8sC0");
// });

// ==================================================
// > CSS
// ==================================================
Files::addStyle("bundle.css");

if (is_admin_bar_showing()) {
    Files::addStyle("admin.css");
}


// ==================================================
// > REMOVE UNWANTED
// ==================================================
// Emojis
add_action("init", function () {
    remove_action("wp_head", "print_emoji_detection_script", 7);
    remove_action("admin_print_scripts", "print_emoji_detection_script");
    remove_action("wp_print_styles", "print_emoji_styles");
    remove_action("admin_print_styles", "print_emoji_styles");
    remove_filter("the_content_feed", "wp_staticize_emoji");
    remove_filter("comment_text_rss", "wp_staticize_emoji");
    remove_filter("wp_mail", "wp_staticize_emoji_for_email");
});


// Ninja forms
add_action("nf_display_enqueue_scripts", function () {
    wp_dequeue_style("nf-display");
});

// Gallery
add_filter( 'use_default_gallery_style', '__return_false' );

// =============================================================================
// > BACK
// =============================================================================

// ==================================================
// > JS
// ==================================================
Files::addScript("admin.js", [], "admin_enqueue_scripts");


// ==================================================
// > CSS
// ==================================================
Files::addStyle("admin.css", [], "admin_enqueue_scripts");

add_action("login_head", function () {
    echo '<link rel="stylesheet" type="text/css" href="' . Files::url("build/css/admin.css") .'" />';
});