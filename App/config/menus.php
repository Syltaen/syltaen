<?php

// ==================================================
// > MENU REGISTRATION
// ==================================================
register_nav_menus([
    "main_menu"   => "Main menu",
    "footer_menu" => "Footer menu",
    "lang_menu"   => "Language menu",
]);

// ==================================================
// > ADMIN - MENU
// ==================================================
add_action("admin_menu", function () {
    // Dashboard
    // remove_menu_page("index.php");

    // Jetpack*
    // remove_menu_page("jetpack");

    // Posts
    remove_menu_page("edit.php");

    // Media
    // remove_menu_page("upload.php");

    // Pages
    // remove_menu_page("edit.php?post_type=page");

    // Comments
    remove_menu_page("edit-comments.php");

    // Appearance
    // remove_menu_page("themes.php");

    // Plugins
    // remove_menu_page("plugins.php");

    // Users
    // remove_menu_page("users.php");

    // Tools
    // remove_menu_page("tools.php");

    //Settings
    // remove_menu_page("options-general.php");
});
