<?php

// ==================================================
// > SCRIPTS FONT END
// ==================================================
add_action( "wp_enqueue_scripts", function () {

	// ========== VENDORS ========== //
	wp_enqueue_script(
		'syltaen.scripts.vendors',
		get_template_directory_uri()."/_2_assets/js/vendors.min.js",
		array('jquery'),
		filemtime(get_stylesheet_directory()."/_2_assets/js/vendors.min.js"),
		true
	);

	// ========== MODULES ========== //
	wp_enqueue_script(
		'syltaen.scripts.modules',
		get_template_directory_uri()."/_2_assets/js/modules.min.js",
		array('syltaen.scripts.vendors'),
		filemtime(get_stylesheet_directory()."/_2_assets/js/modules.min.js"),
		true
	);

	// ========== APP ========== //
	wp_enqueue_script('syltaen.scripts.app',
		get_template_directory_uri()."/_2_assets/js/app.min.js",
		array('syltaen.scripts.vendors', 'syltaen.scripts.modules'),
		filemtime(get_stylesheet_directory()."/_2_assets/js/app.min.js"),
		true
	);

	// ========== AJAXURL ========== //
	wp_add_inline_script(
		'syltaen.scripts.vendors',
		'var ajaxurl = "'.admin_url('admin-ajax.php').'"',
		'before'
	);

} );


// ==================================================
// > STYLES FRONT END
// ==================================================
add_action( "wp_enqueue_scripts", function () {

	wp_enqueue_style(
		'syltaen.styles',
		get_template_directory_uri()."/_2_assets/css/styles.min.css",
		array(),
		filemtime(get_stylesheet_directory()."/_2_assets/css/styles.min.css")
	);

} );

// ==================================================
// > STYLES BACK END
// ==================================================
add_action( "admin_enqueue_scripts", function () {

	wp_enqueue_style(
		'admin.styles',
		get_template_directory_uri().'/_4_styles/_1_setup/admin/admin-style.css',
		array(),
		filemtime(get_stylesheet_directory()."/_2_assets/css/styles.min.css")
	);

} );