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

add_action("wp", function () {
    Data::registerJSVars([
        "var ajaxurl" => admin_url("admin-ajax.php"),
        "var post_id" => get_the_ID(),
        "window.location.site" => site_url("/"),
    ]);
});

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