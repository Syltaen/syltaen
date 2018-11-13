<?php

namespace Syltaen;

// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
Ajax::register("syltaen_ajax_upload", function () {
    wp_send_json(Files::upload($_FILES, false));
});


// ==================================================
// > UPLOAD A FILE WITH AJAX
// ==================================================
Ajax::register("syltaen_ajax_upload_attachment", function () {
    wp_send_json(Files::upload($_FILES, true));
});