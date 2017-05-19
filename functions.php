<?php

namespace Syltaen;

include ("App/Services/Files.php");

// ==================================================
// > FILE LOADING
// ==================================================
spl_autoload_register("Syltaen\Syltaen::autoload");

Syltaen::load( "config", [
	"assets",
	"acf",
	"editor",
	"menus",
	// "taxonomies",
	// "routes",
	// "shortcodes",
	"supports",
	"timber"
]);

Syltaen::load( "vendors", [
	"vendor/autoload"
]);

Syltaen::load( "tools", [
		"shorthands"
]);

Syltaen::load("generators", [
	// "pagination",
	// "breadcrumb",
	// "sharelinks"
]);


// ==================================================
// > POST TYPE REGISTRATION
// ==================================================
News::register();