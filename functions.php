<?php

namespace Syltaen;

// ==================================================
// > Gloabal config
// ==================================================
date_default_timezone_set("Europe/Brussels");


// ==================================================
// > Autoloading & vendors
// ==================================================
require __DIR__ . "/app/Helpers/Files.php";
spl_autoload_register("Syltaen\Files::autoload");
Files::import("app/vendors/vendor/autoload.php");


// ==================================================
// > Custom error-handler
// ==================================================
if (WP_DEBUG) {
    ($handler = (new \Whoops\Handler\PrettyPageHandler))->setEditor("vscode");
    (new \Whoops\Run)
        // ->silenceErrorsInPaths(["/plugins/"], E_ALL)
        ->pushHandler($handler)->register();
}

// ==================================================
// > Import all files not starting with _ in theses directories
// ==================================================
Files::import([
    "app/config",
    "app/hooks/actions",
    "app/hooks/filters",
    "app/hooks/ajax",
]);