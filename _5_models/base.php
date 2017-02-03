<?php

// ==================================================
// > MENUS
// ==================================================
$data["site"]->menus = array(

	"main" => wp_nav_menu(array(
		"theme_location"	=> "main_menu",
		"container"			=> false,
		"echo"				=> false
	)),

	"footer" =>	wp_nav_menu(array(
		"theme_location"	=> "footer_menu",
		"container"			=> false,
		"echo"				=> false
	))

);


// ==================================================
// > HEADER
// ==================================================
$data["site"]->header = array();


// ==================================================
// > FOOTER
// ==================================================
$data["site"]->footer = array();