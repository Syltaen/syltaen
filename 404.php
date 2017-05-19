<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package syltaen
 */

get_header(); ?>

	<div class="p404">
		
		<h2>404</h2>

		<p>
			Cette page n'existe pas <br>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">&#8592; Retour à l'accueil</a>
		</p>

	</div>

<?php get_footer(); ?>
