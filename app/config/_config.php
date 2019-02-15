<?php

return [

    // ==================================================
    // > IDENTITY
    // ==================================================

    // The project name
    "project" => "Hungry Minds - Site web",

    // The client's name
    "client"  => "Hungry Minds",



    // ==================================================
    // > COLORS
    // ==================================================

    // The primary color used in mail templates, excel exports, ...
    "color_primary"   => "#a1f2ca",

    // The secondary color
    "color_secondary" => "#282828",



    // ==================================================
    // > MAILS
    // ==================================================

    // Set to true to prevent mail from being sent
    "mail_debug"     => false,

    // The address all mails are sent from
    "mail_from_addr" => "info@hungryminds.be",

    // The name all mails are sent from
    "mail_from_name" => "Hungry Minds",

    // Define SMTP credentials to send mails, fallback to php mail() when not specified
    "mail_smtp" => [
        "host"     => "",
        "password" => ""
    ],

    // Setup DKIM authentification if provided
    "mail_dkim" => [
        "domain"     => "",
        "selector"   => "phpmailer",
        "private"    => "/var/www/vhosts/.../httpdocs/dkim/dkim.private",
        "passphrase" => ""
    ]

];