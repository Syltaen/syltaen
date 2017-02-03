<?php
/**
 *
 * Controller for the application
 *
 * @package Syltaen
 *
 */

// ==================================================
// > INIT
// ==================================================
$data = Timber::get_context();
$views = array( 'page' );
$models = array( 'page' );

if (is_singular()):
	$data['post'] = Timber::get_post();
	$data['default_content'] = apply_filters('the_content', get_the_content());
endif;


// ==================================================
// > TEMPLATES
// ==================================================

// ========== 404 ========== //
if ( is_404() ):
	$views[] = '404';

// ========== SINGLE ========== //
elseif ( is_single() ):
	$models[] = 'single';
	$views[] = 'single';

	$models[] = 'single-'.$post->post_type;
	$views[] = 'single-'.$post->post_type;

// ========== HOMEPAGE ========== //
elseif ( is_home() || is_front_page() ) :
	$models[] = 'home';
	$views[] = 'home';

endif;

// ==================================================
// > RENDERING
// ==================================================
model("base");
model($models);
render($views, $data);

/* SALUT 1 */