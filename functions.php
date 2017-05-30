<?php

use Syltaen\App\Services\Files;

include("App/Services/Files.php");

// ==================================================
// > Autoloading
// ==================================================
spl_autoload_register("Syltaen\App\Services\Files::autoload");

// ==================================================
// > Files loading
// ==================================================
Files::load("vendors", [
    "vendor/autoload"
]);

Files::load("config", [
    "registrations",
    "supports",
    "menus",
    "acf",
    "editor",
    "routes",
    "shortcodes",
    "assets",
    "timber"
]);

Files::load("actions", [
    // "file"
]);

Files::load("filters", [
    // "file"
]);

Files::load("ajax", [
    "ajax-upload"
]);