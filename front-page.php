<?php
/**
 * The front page template.
 *
 * @package Greenlight
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
else :
	?>
	<section aria-labelledby="welcome-heading">
		<h1 id="welcome-heading"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	</section>
	<?php
endif;

get_footer();
