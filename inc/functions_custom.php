<?php
	function precho($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
	}

	function remove_admin_menu_items() {
		$remove_menu_items = array(__('Comments'), __('Tools'), __('Posts'));
		// , __('Settings'),__('Appearance'),__('Posts'),__('Plugins'),__('Users'),__('Media'),__('Dashboard')
		global $menu;
		end ($menu);
		while (prev($menu)) {
			$item = explode(' ', $menu[key($menu)][0]);
			if (in_array($item[0] != NULL?$item[0]: "" , $remove_menu_items)) {
				unset($menu[ key($menu) ]);
			}
		}
	}

	function create_post_type() {
		register_post_type( 'slt_reals',
			array(
				'labels' => array(
					'name' => __( 'Réalisations' ),
					'singular_name' => __( 'Réalisation' )
				),
				'public' => true,
				'has_archive' => true,
				'rewrite' => array('slug' => 'reals'),
			)
		);
	}


	add_action("admin_menu", "remove_admin_menu_items");
	add_action( 'init', 'create_post_type' );

	if( function_exists('acf_add_options_page') ) {
		acf_add_options_page(array(
			'page_title' 	=> 'Header & Footer',
			'menu_title'	=> 'Header & Footer',
			'menu_slug' 	=> 'headerfooter',
			'capability'	=> 'edit_posts',
			'redirect'		=> false
		));
	}
?>