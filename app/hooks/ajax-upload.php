<?php

namespace Syltaen;

// =============================================================================
// > UPLOAD
// =============================================================================
Hooks::ajax("syltaen_ajax_upload", function () {
    wp_send_json(
        Files::upload(

            // The files
            $_FILES,

            // Generate an attachment
            !empty($_GET["attachment"]) ? true : false,

            // Parent ID
            false,

            // Custom folder
            !empty($_GET["folder"]) ? str_replace("[uniqid]", uniqid(), $_GET["folder"]) : false
        )
    );

});