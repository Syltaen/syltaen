<?php

use Syltaen\App\Services\Fields;

add_filter("timber_context", function ($data) {

    // ==================================================
    // > MENUS
    // ==================================================
    $data["site"]->menus = array(

        "main" => wp_nav_menu(array(
            "theme_location" => "main_menu",
            "container"      => false,
            "echo"           => false
        )),

        "footer" =>	wp_nav_menu(array(
            "theme_location" => "footer_menu",
            "container"      => false,
            "echo"           => false
        )),

        "languages" => qtrans_getSortedLanguages()

    );

    // ==================================================
    // > HEADER
    // ==================================================
    $data["site"]->header = array();
    Fields::store($data["site"]->header, [
        "logo",
        "social"
    ], "headerfooter");


    // ==================================================
    // > FOOTER
    // ==================================================
    $data["site"]->footer = array();
    Fields::store($data["site"]->footer, [
        "contact_title",
        "contact_content",
        "social_title",
        "social_content",
        "bottom_content"
    ], "headerfooter");

    return $data;
});