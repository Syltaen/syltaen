<?php

use Syltaen\App\Services\Files;

// ==================================================
// > FRONT END SCRIPTS
// ==================================================
Files::addScript('vendors.min.js', ['jquery']);

Files::addScript('modules.min.js', ['vendors.min.js']);

Files::addScript('app.min.js', ['modules.min.js']);

Files::addInlineScript(
    "var ajaxurl = '".admin_url('admin-ajax.php')."'",
    'before',
    'vendors.min.js'
);

// ==================================================
// > FRONT END STYLES
// ==================================================
Files::addStyle('styles.min.css');

if (is_admin_bar_showing()) {
    Files::addStyle('admin.min.css');
}


// ==================================================
// > BACK END STYLES
// ==================================================
Files::addStyle('admin.min.css', [], 'admin_enqueue_scripts');

add_action("login_head", function () {
    // echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() .'/_2_assets/css/styles.min.css" />';
    echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() .'/_4_styles/_1_setup/admin/admin-style.css" />';
});

