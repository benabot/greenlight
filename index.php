<?php
/**
 * The main template file (fallback).
 *
 * @package Greenlight
 */

get_header();
?>
<main id="main-content" class="site-main">
<?php

global $wp_query;

$posts_page_id    = (int) get_option( 'page_for_posts' );
$home_title       = $posts_page_id ? get_the_title( $posts_page_id ) : get_bloginfo( 'name' );
$home_description = $posts_page_id ? get_the_excerpt( $posts_page_id ) : get_bloginfo( 'description' );
$home_count       = (int) $wp_query->found_posts;

?>

<section class="archive-intro" aria-labelledby="home-heading">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Actualités', 'greenlight' ); ?></p>
		<h1 id="home-heading"><?php echo esc_html( $home_title ); ?></h1>
		<?php if ( $home_description ) : ?>
			<p class="page-summary"><?php echo esc_html( $home_description ); ?></p>
		<?php endif; ?>
	</header>
	<p class="archive-count">
		<?php
		/* translators: %s: number of articles. */
		$home_count_label = _n( '%s article', '%s articles', $home_count, 'greenlight' );
		printf(
			esc_html( $home_count_label ),
			esc_html( number_format_i18n( $home_count ) )
		);
		?>
	</p>
</section>

<?php if ( have_posts() ) : ?>
	<ul class="post-list" aria-label="<?php esc_attr_e( 'Articles récents', 'greenlight' ); ?>">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
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
					<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
					<p class="entry-more">
						<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lire l’article', 'greenlight' ); ?></a>
					</p>
				</article>
			</li>
		<?php endwhile; ?>
	</ul>

	<?php the_posts_pagination(); ?>
<?php else : ?>
	<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
