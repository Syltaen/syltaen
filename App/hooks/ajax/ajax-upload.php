<?php

// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
add_action("wp_ajax_syltaen_ajax_upload", "syltaen_ajax_upload");
add_action("wp_ajax_nopriv_syltaen_ajax_upload", "syltaen_ajax_upload");
function syltaen_ajax_upload()
{

    $storeFolder = wp_upload_dir();
    $storeFolder = $storeFolder["basedir"] . "/ninja-forms/";
    $time        = $_GET["time"];

    if (!empty($_FILES)) {
        foreach ($_FILES as $key=>$file) {
            $storeFolder = $storeFolder . $key;

            if (!file_exists($storeFolder)) {
                mkdir($storeFolder, 0777, true);
            }

            move_uploaded_file($file["tmp_name"], $storeFolder . "/" . $time . "_" . $file["name"]);
        }
    } else {
        die("No files");
    }

    wp_die();
}