<?php

namespace Syltaen;


add_action("init", function () {

    wp_register_script(
        "syltaen.section",
        Files::url("Controllers/Blocks/core/section/block.section.js"),
        ["wp-blocks", "wp-element"]
    );

    wp_register_style(
        "syltaen.section",
        Files::url("Controllers/Blocks/core/section/block.section.css"),
        ["wp-edit-blocks"],
        filemtime(__DIR__ . "/block.section.css")
    );

    register_block_type("syltaen/section", [
        "editor_script" => "syltaen.section",
        "editor_style"  => "syltaen.section"
    ]);

});