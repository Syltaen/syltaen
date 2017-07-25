<?php

namespace Syltaen;

// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
Ajax::register("upload", function () {
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
});