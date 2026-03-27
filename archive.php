<?php
/**
 * The archive template.
 *
 * @package Greenlight
 */

get_header();

$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();
?>

<section class="archive-intro" aria-labelledby="archive-heading">
	<header class="page-header">
		<h1 id="archive-heading"><?php echo wp_kses_post( $archive_title ); ?></h1>
		<?php if ( $archive_desc ) : ?>
			<p class="page-summary"><?php echo wp_kses_post( $archive_desc ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list" aria-label="<?php esc_attr_e( 'Articles de l’archive', 'greenlight' ); ?>">
			<?php while ( have_posts() ) : the_post(); ?>
				<li class="post-item">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--teaser' ); ?>>
						<header class="entry-header">
							<p class="entry-meta">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							</p>
							<h2 class="entry-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
						</header>
						<?php the_excerpt(); ?>
					</article>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php the_posts_pagination(); ?>

	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
	<?php endif; ?>
</section>

<?php get_footer();
