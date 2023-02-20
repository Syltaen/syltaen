<?php

namespace Syltaen;

// ==================================================
// > MAIL HEADER FILTERS
// ==================================================
add_filter("wp_mail", function ($attrs) {
    if (config("mail.debug")) {
        $attrs["subject"] = "[TEST] " . $attrs["subject"];
    }

    return $attrs;
});

add_filter("wp_mail_from", function () {return config("mail.from.address");}, 99999);
add_filter("wp_mail_from_name", function () {return config("mail.from.name");}, 99999);
add_action("phpmailer_init", "\Syltaen\Mail::init");

// ==================================================
// > DEFAULT WP MAILS
// ==================================================
add_filter("send_password_change_email", "__return_false");
add_filter("send_email_change_email", "__return_false");
if (!function_exists("wp_password_change_notification")) {function wp_password_change_notification()
    {}}