<?php

namespace Syltaen;

// ==================================================
// > FRONT END SCRIPTS
// ==================================================
Files::removeScript("jquery");

Files::addScript("bundle.js");

Files::addInlineScript(
    "var ajaxurl = '".admin_url("admin-ajax.php")."'",
    "before",
    "bundle.js"
);

// ==================================================
// > FRONT END STYLES
// ==================================================
Files::addStyle("bundle.css");

if (is_admin_bar_showing()) {
    Files::addStyle("admin.css");
}


// ==================================================
// > BACK END STYLES
// ==================================================
Files::addStyle("admin.css", [], "admin_enqueue_scripts");

add_action("login_head", function () {
    echo '<link rel="stylesheet" type="text/css" href="' . Files::url("css", "bundle.css") .'" />';
    echo '<link rel="stylesheet" type="text/css" href="' . Files::url("css", "admin.css") .'" />';
});

