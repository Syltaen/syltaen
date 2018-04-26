<?php

namespace Syltaen;

// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
Ajax::register("syltaen_ajax_upload", function () {

    // No file -> error
    if (empty($_FILES)) die(false);

    // File -> upload and send result
    wp_send_json(Files::upload($_FILES, false, true));

});