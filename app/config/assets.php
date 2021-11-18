<?php

namespace Syltaen;

// =============================================================================
// > FRONT
// =============================================================================

// ==================================================
// > JS
// ==================================================
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
add_action("wp_enqueue_scripts", function () {
    wp_dequeue_style("nf-font-awesome");
    wp_dequeue_style("wc-blocks-vendors-styles");
    wp_dequeue_style("wc-blocks-style");
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