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
    return View::menu($id, "inner-menu");
});

// YEAR
add_shortcode("year", function () {
    return Time::current("Y");
});

// ==================================================
// > COOKIES
// ==================================================
add_shortcode("cookie_table", function () {
    return do_shortcode("[cky_outside_audit_table]") . '<p><span class="button manage-cookies">' . __("Gérer les cookies", "syltaen") . '</span></p>';
});