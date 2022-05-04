<?php

namespace Syltaen;

// =============================================================================
// > GLOBALS
// =============================================================================

// PAGE TITLE
add_shortcode("page_title", function () {
    return get_the_title();
});

// MENUS
add_shortcode("menu", function ($atts, $content = null) {
    extract(shortcode_atts(["id" => null], $atts));
    return View::menu($id);
});

// YEAR
add_shortcode("year", function () {
    return Time::current("Y");
});