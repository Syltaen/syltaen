<?php

namespace Syltaen;

// ==================================================
// > MAIL HEADER FILTERS
// ==================================================

// Send all default wp_mail via the Mail helpder
add_filter("wp_mail", "\Syltaen\Mail::hookRelay");

// ==================================================
// > DEFAULT WP MAILS
// ==================================================
add_filter("send_password_change_email", "__return_false");
add_filter("send_email_change_email", "__return_false");