<?php

namespace Syltaen;

// ==================================================
// > TIMEZONE
// ==================================================
date_default_timezone_set("Europe/Brussels");

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
// if (WP_DEBUG) {
//     $handler = new \Whoops\Handler\PrettyPageHandler;
//     $handler->setEditor("vscode");

//     (new \Whoops\Run)
//         // ->silenceErrorsInPaths(["/plugins/"], E_ALL)
//         ->pushHandler($handler)
//         ->register();
// }


// ==================================================
// > FILES LOADING
// ==================================================
Files::import("app/config", [
    "acf",
    "gutenberg",
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




// ==================================================
// > TMP
// ==================================================

Files::import("Controllers/Blocs/core/section", "block.section");

// add_filter("allowed_block_types", function () {
//     return [
//         // "core/paragraph",
//         "syltaen/section"
//     ];
// });




return;
// Register a testimonial ACF Block
if( function_exists('acf_register_block') ) {

    $result = acf_register_block(array(
        'name'				=> 'testimonial',
        'title'				=> __('Testimonial'),
        'description'		=> __('A custom testimonial block.'),
        'render_callback'	=> function () {
            $testimonial = "salut";
            $author = "ouioiuioiu";

            ?>
            <blockquote class="testimonial">
                <p><?php echo $testimonial; ?></p>
                <cite>
                    <span><?php echo $author; ?></span>
                </cite>
            </blockquote>
            <?php


        }
        //'category'		=> '',
        //'icon'			=> '',
        //'keywords'		=> array(),
    ));
}