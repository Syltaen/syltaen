<?php

/*
 * https://codex.wordpress.org/Function_Reference/register_taxonomy
 */

register_taxonomy("", array("posts"), array(
	"labels" => array(
		"name" => "Nom",
	),
	"public" => true,
	"show_admin_column" => true,
	'hierarchical' => true
));