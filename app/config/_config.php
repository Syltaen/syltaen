<?php

return [

    "timezone" => "Europe/Brussels",

    // ==================================================
    // > IDENTITY
    // ==================================================
    // The project name
    "project"  => "",

    // The client's name
    "client"   => "",

    // ==================================================
    // > COLORS
    // ==================================================
    // The colors used in mail templates, excel exports, ...
    "color"    => [
        "primary"   => "#111",
        "secondary" => "#555",
    ],

    // ==================================================
    // > MAILS
    // ==================================================
    "mail"     => [

        // Set to true to prevent mail from being sent
        "debug" => false || (defined("LOCAL_ENV") && LOCAL_ENV),

        // The address all mails are sent from
        "from"  => [
            "name"    => "",
            "address" => "",
        ],

        // Define SMTP credentials to send mails, fallback to php mail() when not specified
        "smtp"  => [
            "host"     => "",
            "username" => "",
            "password" => "",
            "debug"    => false,
        ],

        // Setup DKIM authentification if provided
        "dkim"  => [
            "domain"     => "",
            "selector"   => "phpmailer",
            "private"    => "/var/www/vhosts/.../httpdocs/dkim/dkim.private",
            "passphrase" => "",
        ],
    ],

    // ==================================================
    // > DEBUGGING
    // ==================================================
    "debug"    => [

        // Number of backtrace calls to include in debugs
        "backtrace_level" => 1,

        // Wether to fetch all fields of model logged or not.
        // Can cause infinite loop in some cases.
        "fetch_fields"    => true,

        // Number of lines to keep in each logfile
        "log_history"     => 50000,
    ],
];