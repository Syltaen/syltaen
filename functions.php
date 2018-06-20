<?php

namespace Syltaen;

// ==================================================
// > CLASSES & ASSETS LOADING
// ==================================================
require __DIR__ . "/app/Helpers/Files.php";
spl_autoload_register("Syltaen\Files::autoload");

// ==================================================
// > COMPOSER AUTOLOADER
// ==================================================
Files::import("app/vendors", "vendor/autoload");

// ==================================================
// > ERROR HANDLING
// ==================================================
if (WP_DEBUG) {
    $handler = new \Whoops\Handler\PrettyPageHandler;
    $handler->setEditor("vscode");

    (new \Whoops\Run)
        // ->silenceErrorsInPaths(["/plugins/"], E_ALL)
        ->pushHandler($handler)
        ->register();
}


// ==================================================
// > FILES LOADING
// ==================================================
Files::import("app/config", [
    "acf",
    "globals",
    "registrations",
    "supports",
    "menus",
    "editor",
    "routes",
    "shortcodes",
    "assets"
]);

Files::import("app/hooks/actions", [
    "actions-lang",
    // "actions-users",
    // "actions-cron",
    // "actions-posts",
]);

Files::import("app/hooks/filters", [
    "filters-mails"
]);

Files::import("app/hooks/ajax", [
    "ajax-upload"
]);