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

add_filter("wp_mail_content_type", function () {
    return Mail::$contType;
});


// ==================================================
// > PASSWORD RETRIEVE MAIL
// ==================================================
add_filter("retrieve_password_message", function ($message, $key) {

    $user_data = "";

    // If no value is posted, return false
    if (!isset($_POST["user_login"])) return "";

    // Fetch user information from user_login
    if (strpos($_POST["user_login"], "@")) {
        $user_data = get_user_by("email", trim($_POST["user_login"]));
    } else {
        $user_data = get_user_by("login", trim($_POST["user_login"]));
    }

    if (!$user_data) return "";

    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;

    // Setting up message for retrieve password
    $message = "<p>Bonjour,<p>";
    $message .= "<p>Vous avez demandé le renouvèlement du mot de passe pour ce compte: <strong>$user_login</strong></p>";
    $message .= "<p>Pour renouveler votre mot de passe, veuillez cliquer sur le lien suivant :<br>";
    $message .= '<a href="';
    $message .= site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), "login");
    $message .= '">';
    $message .= site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), "login");
    $message .= '</a></p>';
    $message .= "<p>S’il s’agit d’une erreur, ignorez ce message et la demande ne sera pas prise en compte.</p><br>";
    $message .= "<p>Sincères salutations, l'équipe ".Mail::$fromAddr.".</p>";

    // Return completed message for retrieve password
    return $message;

}, 10, 2);