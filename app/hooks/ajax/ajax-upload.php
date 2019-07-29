<?php

namespace Syltaen;

// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
Ajax::register("syltaen_ajax_upload", function () {

    wp_send_json(
        Files::upload(

            // The files
            $_FILES,

            // Generate an attachment
            !empty($_POST["attachement"]) ? true : false,

            // Parent ID
            false,

            // Custom folder
            !empty($_POST["folder"]) ? str_replace("[uniqid]", uniqid(), $_POST["folder"]): false

        )
    );

});