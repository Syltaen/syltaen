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