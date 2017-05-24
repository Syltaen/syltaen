<?php

/*
 * https://codex.wordpress.org/Function_Reference/register_taxonomy
 */

// ==================================================
// > LOCATIONS
// ==================================================
register_taxonomy("location-type", null, array
(
    "labels" => array(
        "name" => "Location types",
    ),
    "public"            => true,
    "show_admin_column" => true,
    "hierarchical"      =>  true
));