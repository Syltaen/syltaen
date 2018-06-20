<?php

namespace Syltaen;

// ==================================================
// > WRONG LOGIN
// ==================================================
add_action("wp_login_failed", function ($username) {

    Data::nextPage([
        "error_message" =>
            __("Votre adresse e-mail ou votre mot de passe est incorrect.", "syltaen")
            ."<br>".__("Mot de passe perdu ?", "syltaen")." <a class='underlined' href='".wp_lostpassword_url(site_url("connexion"))."'>".__("Demander un nouveau mot de passe", "syltaen")."</a>"
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
                        __("Votre compte doit être validé pour pouvoir vous connecter.", "syltaen")
                ], "connexion");
                break;
            default: break;
        }
    }
});