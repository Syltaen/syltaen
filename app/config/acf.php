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
        "icon_url"   => "dashicons-align-center",
        "position"   => 4,
    ]);

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
    acf_update_setting("google_api_key", "__PROVIDE_A_NEW_KEY__");
});



// ==================================================
// > ADD ACF DATA TO SEARCH
// ==================================================
/**
 * Extend WordPress search to include custom fields
 * http://adambalee.com
 *
 * Join posts and postmeta tables
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
add_filter("posts_join", function ($join) {
    global $wpdb;
    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
});

/**
 * Modify the search query with posts_where
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
add_filter("posts_where", function ($where) {
    global $pagenow, $wpdb;
    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }
    return $where;
});

/**
 * Prevent duplicates
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
add_filter("posts_distinct", function ($where) {
    global $wpdb;
    if ( is_search() ) {
        return "DISTINCT";
    }
    return $where;
});