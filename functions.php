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
// > 2 - VENDORS
// ==================================================
require_once('_1_functions/_2_vendors/vendor/autoload.php');


// ==================================================
// > 3 - TOOLS
// ==================================================
require_once('_1_functions/_3_tools/mvc.php');
require_once('_1_functions/_3_tools/chromephp.php');


// ==================================================
// > 4 - GENERATORS
// ==================================================
// require_once('_1_functions/_4_generators/breadcrumb.php');
// require_once('_1_functions/_4_generators/pagenav.php');
// require_once('_1_functions/_4_generators/sharelinks.php');


// ==================================================
// > 5 - HOOKS
// ==================================================


// ==================================================
// > 6 - AJAX
// ==================================================

























