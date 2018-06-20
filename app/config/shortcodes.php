<?php

namespace Syltaen;

// ==================================================
// > PAGE TITLE
// ==================================================
add_shortcode("page_title", function () {
    return get_the_title();
});


// ==================================================
// > MENUS
// ==================================================
add_shortcode("menu", function ($atts, $content = null) {
    extract(shortcode_atts(["id" => null], $atts));

    return wp_nav_menu([
        "menu"      => $id,
        "container" => "nav",
        "echo"      => false
    ]);
});


// ==================================================
// > LOGIN FORM
// ==================================================
add_shortcode("login_form" , function ($atts, $content = null) {
    $atts["landing"] = isset($atts["landing"]) ? $atts["landing"] : "connexion";

    $form = wp_login_form([
        "echo"           => false,
        "label_username" => __("Adresse e-mail", "syltaen"),
        "label_password" => __("Mot de passe #reset#", "syltaen"),
        "redirect"       => get_the_permalink(get_page_by_path($atts["landing"]))
    ]);

    $form = str_replace(
        "#reset#",
        "<a href='".wp_lostpassword_url(get_the_permalink(get_page_by_path("connexion")))."'>".__("Mot de passe oublié ?", "syltaen")."</a>",
        $form
    );

    return $form;
});


// ==================================================
// > FORMS
// ==================================================
// add_shortcode("ninja_form", function ($atts) {
//     return "<div class='nf-form-loader' data-id='".$atts["id"]."'></div>";
// });