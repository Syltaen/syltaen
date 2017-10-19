<?php

namespace Syltaen;

// ==================================================
// > MAIL HEADER FILTERS
// ==================================================
add_filter("wp_mail_from_name", function () {
    return Mail::$fromName;
});

add_filter("wp_mail_from", function () {
    return Mail::$fromAddr;
});