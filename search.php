<?php
/**
 * The search results template.
 * Noindexed via inc/seo.php (added in Phase 5).
 *
 * @package Greenlight
 */

get_header();
?>
<main id="main-content" class="site-main">
<section aria-labelledby="search-heading">
	<header>
		<h1 id="search-heading">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Résultats pour : %s', 'greenlight' ),
				'<em>' . esc_html( get_search_query() ) . '</em>'
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination(); ?>

	<?php else : ?>
		<p><?php esc_html_e( 'Aucun résultat.', 'greenlight' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</section>

<?php
get_footer();
