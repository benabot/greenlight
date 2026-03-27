<?php
/**
 * The main template file (fallback).
 *
 * @package Greenlight
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
		</article>
		<?php
	endwhile;

	the_posts_pagination();
else :
	?>
	<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
	<?php
endif;

get_footer();
