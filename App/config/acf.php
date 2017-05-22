<?php

// ==================================================
// > ACF OPTIONS PAGES
// ==================================================
if (function_exists('acf_add_options_page')) {

    acf_add_options_page([
        'page_title' => 'Header & Footer',
        'menu_title' => 'Header & Footer',
        'menu_slug'  => 'headerfooter',
        'post_id'    => 'headerfooter',
        'capability' => 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-align-center',
        'position'   => 4,
    ]);

}