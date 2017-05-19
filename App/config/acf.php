<?php

// ==================================================
// > ACF OPTIONS PAGES
// ==================================================
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Header & Footer',
		'menu_title'	=> 'Header & Footer',
		'menu_slug' 	=> 'headerfooter',
		'capability'	=> 'edit_posts',
		'redirect'		=> false,
		'icon_url'		=> 'dashicons-align-center',
		'position'		=> 4
	));

}