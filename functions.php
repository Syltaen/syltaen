<?php

namespace Syltaen;

// ==================================================
// > Autoloading & vendors
// ==================================================
require __DIR__ . "/app/Helpers/Files.php";
spl_autoload_register("Syltaen\Files::autoload");
Files::import("app/vendors/vendor/autoload.php");

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
    "app/hooks",
]);

// ==================================================
// > TMP
// ==================================================

Files::import("Controllers/Blocks/core/section", "block.section.php");

// add_filter("allowed_block_types", function () {
//     return [
//         // "core/paragraph",
//         "syltaen/section"
//     ];
// });

// return;
// Register a testimonial ACF Block
if (function_exists('acf_register_block')) {

    $result = acf_register_block([
        'name'            => 'testimonial',
        'title'           => __('Testimonial'),
        'description'     => __('A custom testimonial block.'),
        'render_callback' => function () {
            $testimonial = "salut";
            $author      = "ouioiuioiu";

            ?>
            <blockquote class="testimonial">
                <p><?php echo $testimonial; ?></p>
                <cite>
                    <span><?php echo $author; ?></span>
                </cite>
            </blockquote>
            <?php

        },
        //'category'        => '',
        //'icon'            => '',
        //'keywords'        => array(),
    ]);
}