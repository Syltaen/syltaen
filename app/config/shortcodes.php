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

    return wp_nav_menu([
        "menu"      => $id,
        "container" => "nav",
        "echo"      => false
    ]);
});


// =============================================================================
// > FORMS
// =============================================================================

// LOGIN
add_shortcode("login_form" , function ($atts, $content = null) {
    if ($ref = Route::query("ref")) {
        $landing = get_the_permalink($ref) . "?" . $_SERVER["QUERY_STRING"];
    } else {
        $landing = isset($atts["landing"]) ? $atts["landing"] : "";
        $landing = get_page_by_path($landing);
        $landing = $landing ? get_the_permalink($landing) : site_url();
    }

    $form = wp_login_form([
        "echo"           => false,
        "label_username" => __("Adresse e-mail", "syltaen"),
        "label_password" => __("Mot de passe #reset#", "syltaen"),
        "redirect"       => $landing,
        "remember"       => isset($atts["remember"]) ? $atts["remember"] : true
    ]);

    // $form = str_replace(
    //     "#reset#",
    //     "<a tabindex='-1' class='user_resetpass' href='".site_url("oubli-mot-de-passe")."'>".__("Mot de passe oublié ?", "syltaen")."</a>",
    //     $form
    // );

    return $form;
});

// NINJA FORMS
add_shortcode("ninja_form", function ($atts) {
    return "<div class='nf-form-loader' data-id='".$atts["id"]."'></div>";
});