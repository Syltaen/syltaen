<?php

namespace Syltaen;

// ==================================================
// > CLASSES & ASSETS LOADING
// ==================================================
$syltaen_paths = require("app/config/paths.php");
include($syltaen_paths["classes"]["Files"]."/"."Files.php");
spl_autoload_register("Syltaen\Files::autoload");

// ==================================================
// > Files loading
// ==================================================
Files::load("vendors", [
    "vendor/autoload"
]);

Files::load("config", [
    "globals",
    "registrations",
    "supports",
    "menus",
    "acf",
    "editor",
    "routes",
    "shortcodes",
    "assets"
]);

Files::load("actions", [
    // "actions-users",
    // "actions-cron",
    // "actions-posts",
]);

Files::load("filters", [
    "filters-mails"
]);

Files::load("ajax", [
    "ajax-upload"
]);