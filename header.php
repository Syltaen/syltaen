<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package syltaen
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles/styles.min.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles/responsive.min.css" />

		<script src="<?php echo get_template_directory_uri(); ?>/scripts/jquery.min.js"></script>
		<script src="<?php echo get_template_directory_uri(); ?>/scripts/main.js?v=1.0"></script>

		<?php 
			wp_head(); 
		?>
	</head>

	<body <?php body_class(); ?>>
		
		<header role="banner">
			<nav role="navigation">
				<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu' ) ); ?>			
			</nav>
		</header>