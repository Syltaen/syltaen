<?php

// ==================================================
// > MCE CUSTOM STYLES
// ==================================================
add_filter( "tiny_mce_before_init", function ( $init_array ) {

	$style_formats = array(
		/* ========= TITLES ========= */
		array(
			"title"=> "Titre",
			"items" => array(
				array(
					'title' => 'Titre 1 - H1',
					'block' => 'h1',
					'wrapper' => false
				),
				array(
					'title' => 'Titre 2 - H2',
					'block' => 'h2',
					'wrapper' => false
				),
				array(
					'title' => 'Titre 3 - H3',
					'block' => 'h3',
					'wrapper' => false
				),
				array(
					'title' => 'Titre 4 - H4',
					'block' => 'h4',
					'wrapper' => false
				),
				array(
					'title' => 'Titre 5 - H5',
					'block' => 'h5',
					'wrapper' => false
				),
				array(
					'title' => 'Titre 6 - H6',
					'block' => 'h6',
					'wrapper' => false
				),
				array(
					'title' => 'Sous-titre',
					'inline' => 'span',
					'classes' => 'subtitle',
					'wrapper' => false
				),
			)
		),

		/* ========= CALLS ========= */
		array(
			"title"=> "Liens",
			"items" => array(
				array(
					'title' => 'Bouton',
					'selector' => 'a',
					'classes' => 'button',
					'wrapper' => false
				),
				array(
					'title' => 'Lien à crochets',
					'selector' => 'a',
					'classes' => 'croched-link',
					'wrapper' => false
				)
			)
		),

		/* ========= TEXTES ========= */
		array(
			"title"=> "Textes",
			"items" => array(
				array(
					'title' => 'Couleur : Principale',
					'inline' => 'span',
					'classes' => 'main-color'
				),
				array(
					'title' => 'Couleur : Secondaire',
					'inline' => 'span',
					'classes' => 'secondary-color'
				),
				array(
					'title' => 'Citation',
					'block' => 'blockquote',
					'wrapper' => true
				),
				array(
					'title' => 'Citation - Source',
					'block' => 'cite',
					'wrapper' => true
				)
			)
		),

		/* ========= LISTES ========= */
		array(
			"title"=> "Listes",
			"items" => array(
				array(
					'title' => 'Liste sans style',
					'selector' => 'ul',
					'classes' => 'unstyled'
				)
			)
		),

		/* ========= IMAGES ========= */
		array(
			"title"=> "Images",
			"items" => array(
				array(
					'title' => 'Bord blanc',
					'selector' => 'img',
					'classes' => 'white-border',
					'wrapper' => false
				),
				array(
					'title' => 'Rond',
					'selector' => 'img',
					'classes' => 'round',
					'wrapper' => false
				),
				array(
					'title' => 'Taille - Toute la largueur',
					'selector' => 'img',
					'classes' => 'full-width',
					'wrapper' => false
				),
				array(
					'title' => 'Taille - Toute la hauteur',
					'selector' => 'img',
					'classes' => 'full-height',
					'wrapper' => false
				),
			)
		),


	);
	$init_array['style_formats_merge'] = false;
	$init_array['style_formats'] = json_encode( $style_formats );
	return $init_array;

} );


// ==================================================
// > EDITOR STYLESHEET
// ==================================================
add_editor_style( "_2_assets/css/styles.min.css" );