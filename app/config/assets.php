<?php

namespace Syltaen;

// =============================================================================
// > FRONT
// =============================================================================

// ==================================================
// > JS
// ==================================================
Files::removeScript("jquery");

Files::addScript("bundle.js");

Files::addInlineScript(
    "var ajaxurl = '".admin_url("admin-ajax.php")."';".
    "window.location.site = '".site_url("/")."';",
    "before",
    "bundle.js"
);

// ==================================================
// > CSS
// ==================================================
Files::addStyle("bundle.css");

if (is_admin_bar_showing()) {
    Files::addStyle("admin.css");
}


// =============================================================================
// > BACK
// =============================================================================

// ==================================================
// > JS
// ==================================================
Files::addScript("admin.js", [], "admin_enqueue_scripts");


// ==================================================
// > CSS
// ==================================================
Files::addStyle("admin.css", [], "admin_enqueue_scripts");

add_action("login_head", function () {
    echo '<link rel="stylesheet" type="text/css" href="' . Files::url("css", "bundle.css") .'" />';
    echo '<link rel="stylesheet" type="text/css" href="' . Files::url("css", "admin.css") .'" />';
});


