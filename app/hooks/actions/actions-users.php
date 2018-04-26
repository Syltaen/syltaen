<?php

namespace Syltaen;

// ==================================================
// > WRONG LOGIN
// ==================================================
add_action("wp_login_failed", function ($username) {

    Data::nextPage([
        "error_message" =>
            "Votre adresse e-mail ou votre mot de passe est incorrect."
            ."<br>Mot de passe perdu ? <a class='underlined' href='".wp_lostpassword_url(site_url("connexion"))."'>Demander un nouveau mot de passe</a>"
    ], "connexion");

});

// ==================================================
// > LOG OUT IF NOT VALID
// ==================================================
add_action("wp", function () {
    $user = Data::globals("user");

    if ($user) {
        switch ($user->getOne()->status) {

            case "to_validate":
            case "refused":
                Users::logout();
                Data::nextPage([
                    "error_message" =>
                        "Votre compte doit être validé pour pouvoir vous connecter."
                ], "connexion");
                break;
            default: break;
        }
    }
});