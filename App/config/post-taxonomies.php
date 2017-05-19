<?php

/*
 * https://codex.wordpress.org/Function_Reference/register_taxonomy
 */

// ==================================================
// > NAME
// ==================================================
register_taxonomy("", null, array(
	"labels" => array(
		"name" => "Nom",
	),
	"public" => true,
	"show_admin_column" => true,
	'hierarchical' => true
));