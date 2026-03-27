<?php
/**
 * The main template file (fallback).
 *
 * @package Greenlight
 */

get_header();

$site_title       = get_bloginfo( 'name' );
$site_description = get_bloginfo( 'description' );
?>

<section class="archive-intro" aria-labelledby="home-heading">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Actualités', 'greenlight' ); ?></p>
		<h1 id="home-heading"><?php echo esc_html( $site_title ); ?></h1>
		<?php if ( $site_description ) : ?>
			<p class="page-summary"><?php echo esc_html( $site_description ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list" aria-label="<?php esc_attr_e( 'Articles récents', 'greenlight' ); ?>">
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

get_footer();
