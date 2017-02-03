<?php

// ==================================================
// > 1 - SETUP
// ==================================================
add_action( "after_setup_theme", function () {
	require_once('_1_functions/_1_setup/acf.php');
	require_once('_1_functions/_1_setup/assets.php');
	require_once('_1_functions/_1_setup/editor.php');
	require_once('_1_functions/_1_setup/menus.php');
	// require_once('_1_functions/_1_setup/post-types.php');
	// require_once('_1_functions/_1_setup/post-taxonomies.php');
	// require_once('_1_functions/_1_setup/post-status.php');
	// require_once('_1_functions/_1_setup/routes.php');
	// require_once('_1_functions/_1_setup/shortcodes.php');
	require_once('_1_functions/_1_setup/supports.php');
} );

// ==================================================
// > 7 - VENDORS
// ==================================================
require_once('_1_functions/_7_vendors/vendor/autoload.php');


// ==================================================
// > 2 - TOOLS
// ==================================================
require_once('_1_functions/_2_tools/mvc.php');
require_once('_1_functions/_2_tools/chromephp.php');


// ==================================================
// > 3 - GENERATORS
// ==================================================
// require_once('_1_functions/_3_generators/breadcrumb.php');
// require_once('_1_functions/_3_generators/pagenav.php');


// ==================================================
// > 4 - ACTIONS
// ==================================================



// ==================================================
// > 5 - FILTERS
// ==================================================



// ==================================================
// > 6 - AJAX
// ==================================================

























