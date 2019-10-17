<?php

/*
 * Plugin Name: Syltaen register
 * Plugin URI: http://hungryminds.be
 * Description: Actions that should be launched during plugin registration
 * Version: 1.1.0
 * Author: Stanley Lambot
 * Author URI: http://hungryminds.be
 * Text Domain: syltaen
 *
 * Copyright 2017 Stanley Lambot.
 */

// ==================================================
// > FORMS ACTION/FIELDS REGISTRATION
// ==================================================
require __DIR__ . "/form_register.php";
new \Syltaen\FormRegisterer();


// ==================================================
// > CLI COMMANDS
// ==================================================
if (defined("WP_CLI")  && WP_CLI) {
    require get_stylesheet_directory() . "/app/lib/CLI/BaseCLI.php";
    require get_stylesheet_directory() . "/app/Helpers/CLI.php";
    \WP_CLI::add_command("syltaen", "\Syltaen\CLI");
}