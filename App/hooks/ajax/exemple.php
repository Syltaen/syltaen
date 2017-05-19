<?php

// ==================================================
// > AJAX EXEMPLE
// ==================================================

add_action('wp_ajax_syltaen_exemple_ajax', 'syltaen_exemple_ajax');
add_action('wp_ajax_nopriv_syltaen_exemple_ajax', 'syltaen_exemple_ajax');
function syltaen_exemple_ajax()
{
    /* DO STUFF */
    wp_die();
}