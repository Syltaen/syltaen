<?php

namespace Syltaen;

/*
 * Load the textdomain : syltaen
 */
add_action("after_setup_theme", function () {
    load_theme_textdomain("syltaen", Files::path("app/lang"));
});

/*
 * Scan all pug files for translations and add them in app/lang/view-strings.php
 */
add_action("loco_admin_init", function () {
    // Only scan of the template edit page
    if (empty($_GET["path"]) || !strpos($_GET["path"], "syltaen.pot")) {
        return false;
    }

    Files::scanPugTranslations();
});
