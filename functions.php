<?php

namespace Syltaen;

// ==================================================
// > Autoloading & vendors
// ==================================================
require __DIR__ . "/app/Helpers/Files.php";
spl_autoload_register("Syltaen\Files::autoload");
Files::import("app/vendors/vendor/autoload.php");


// ==================================================
// > TIMEZONE
// ==================================================
Time::setDefaultTimezone();


// ==================================================
// > Custom error-handler
// ==================================================
if (WP_DEBUG || isset($_GET["debug"])) {
    ($handler = (new \Whoops\Handler\PrettyPageHandler))->setEditor("vscode");
    (new \Whoops\Run)
        ->silenceErrorsInPaths(["/plugins/", "/wp-admin/", "/wp-includes/"], E_ALL)
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