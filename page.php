<?php
/**
 * The page template.
 *
 * @package Greenlight
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h1><?php the_title(); ?></h1>
			</header>
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</article>
		<?php

		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
endif;

get_footer();
