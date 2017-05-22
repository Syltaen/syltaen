<?php

// ==================================================
// > PAGE TITLE
// ==================================================
add_shortcode('page_title', function () {
    return get_the_title();
});

// ==================================================
// > LOGIN FORM
// ==================================================
add_shortcode('login_form' , function ($atts, $content = null) {
    $atts['landing'] = isset($atts['landing']) ?: 'dashboard';

    $form = wp_login_form( array(
        'echo'           => false,
        'label_username' => 'Adresse e-mail',
        'label_password' => 'Mot de passe #reset#',
        'redirect'       => get_the_permalink(get_page_by_path( $atts['landing'] ))
    ));

    $form = str_replace('#reset#', "<a href='".wp_lostpassword_url(get_the_permalink(get_page_by_path('connexion')))."'>Mot de passe oublié ?</a>", $form);
    return $form;
});

// ==================================================
// > SOCIAL MENU
// ==================================================
add_shortcode('header_social', function () {
    return '';
    //     return $compiler->render('
    // nav.social: ul
    //     each soc in social
    //         li
    //             a(href=soc.soc_url target="_blank")
    //                 != soc.soc_icon
    //                 span= soc.soc_name
    //     ', array(
    //         "social" => get_field('social', 'headerfooter')
    //     ));
} );
