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

<section aria-labelledby="archive-heading">
	<header>
		<h1 id="archive-heading"><?php echo wp_kses_post( $archive_title ); ?></h1>
		<?php if ( $archive_desc ) : ?>
			<p><?php echo wp_kses_post( $archive_desc ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination(); ?>

	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
	<?php endif; ?>
</section>

<?php get_footer();
