<?php
/**
 *
 * Template Name: Page de contenu standard
 *
 * @package syltaen
 */

get_header(); ?>

	<div class="container">

		<div id="sidebar">
			<?php get_sidebar(); ?>
		</div>

		<div id="content">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php the_content(); ?>
			<?php endwhile; // end of the loop. ?>

		</div>
	</div><!-- .container -->

<?php get_footer(); ?>
