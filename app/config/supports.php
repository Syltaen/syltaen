<?php

namespace Syltaen;

add_action("after_setup_theme", function () {
    // ==================================================
    // > THEME SUPPORTS
    // ==================================================
    // add_theme_support("automatic-feed-links");
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("breadcrumb-trail");

    // ==================================================
    // > IMAGE SIZES
    // ==================================================
    /**
     * @see https://developer.wordpress.org/reference/functions/add_image_size/
     */
    add_image_size("full-width", 9999, 9999);
    // add_image_size("archive", 380, 250, true);
    // update_option("medium_crop", 1);

    // add_filter("big_image_size_threshold", "__return_false");
    add_filter("intermediate_image_sizes_advanced", function ($sizes) {
        $keep = ["thumbnail", "medium", "large"];
        return array_filter($sizes, function ($key) use ($keep) {
            return in_array($key, $keep);
        }, ARRAY_FILTER_USE_KEY);
    });

    // ==================================================
    // > UPLOADS
    // ==================================================
    add_filter("upload_mimes", function ($existing_mimes = []) {
        $existing_mimes["eps"] = "application/postscript";
        $existing_mimes["zip"] = "application/zip";
        $existing_mimes["ai"]  = "application/postscript";
        $existing_mimes["svg"] = "image/svg+xml";
        return $existing_mimes;
    });

    // ==================================================
    // > ROLES & PERMISSIONS
    // ==================================================
    Users::unregisterRoles([
        "contributor",
        "author",
        "subscriber",
        // "editor"
    ]);

    // Users::registerCapabilities([
    //     "custom_capabitily_to_add"
    // ]);

    // ==================================================
    // > FIXES
    // ==================================================
    remove_action("shutdown", "wp_ob_end_flush_all", 1);
    add_filter("run_wptexturize", "__return_false");
});
