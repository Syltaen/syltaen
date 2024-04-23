<?php

namespace Syltaen;

Route::add([

    // ==================================================
    // > API
    // ==================================================
    "api" => 'api/([a-zA-Z]+)/?([^/]+)?/?([^/]+)?/?$',

    // ==================================================
    // > EXEMPLE
    // ==================================================
    // [
    //     'NAME_OF_THE_PAGE/(things|to|capture)/?$',
    //     'index.php?pagename=NAME_OF_THE_PAGE&(TOKEN_NAME)=$matches[1]'
    // ],

]);

// ==================================================
// > CATCHALL PAGINATION
// ==================================================
/**
 * Remove "page" from pagination base
 */
// add_action("init", function () {
//     global $wp_rewrite;
//     $wp_rewrite->pagination_base = "";
// });

/**
 * Add catchall pagination route
 */
// Route::add([[
//     "(.+)/([0-9]*)/?$",
//     'index.php?pagename=$matches[1]&paged=$matches[2]',
// ]]);

// ==================================================
// > FILTER GLOBAL QUERY VARS
// ==================================================
// add_filter("query_vars", function ($vars) {
//     return is_admin() ? $vars : array_diff($vars, ["s"]);
// });

// ==================================================
// > CUSTOM TERM LINK
// ==================================================
// add_filter("term_link", function ($termlink, $term, $taxonomy) {
//     if ($taxonomy == NewsTaxonomy::SLUG) {
//         return News::getArchiveURL("?type={$term->slug}");
//     }
//     return $term;
// }, 10, 3);
