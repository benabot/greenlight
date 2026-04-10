<?php
/**
 * The page template.
 *
 * @package Greenlight
 */

get_header();
?>
<main id="main-content" class="site-main">
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>
			<header class="entry-header">
				<h1><?php the_title(); ?></h1>
			</header>
			<section class="entry-content">
				<?php the_content(); ?>
			</section>
			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Pages de la page', 'greenlight' ) . '">',
					'after'  => '</nav>',
				)
			);
			?>
		</article>
		<?php

		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
endif;

get_footer();
