<?php
/**
 * The posts index template.
 *
 * @package Greenlight
 */

get_header();

global $wp_query;

$posts_page_id    = (int) get_option( 'page_for_posts' );
$home_title       = $posts_page_id ? get_the_title( $posts_page_id ) : get_bloginfo( 'name' );
$home_lead        = greenlight_get_archive_lead_text( 'home' );
$home_count       = (int) $wp_query->found_posts;
?>

<section class="archive-intro" aria-labelledby="home-heading">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Actualités', 'greenlight' ); ?></p>
		<h1 id="home-heading"><?php echo esc_html( $home_title ); ?></h1>
		<p class="archive-lead"><?php echo esc_html( $home_lead ); ?></p>
	</header>
	<p class="archive-count">
		<?php
		printf(
			esc_html( _n( '%s article', '%s articles', $home_count, 'greenlight' ) ),
			esc_html( number_format_i18n( $home_count ) )
		);
		?>
	</p>
</section>

<?php if ( have_posts() ) : ?>
	<?php
	the_post();
	$home_first_categories = get_the_category_list( ', ' );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--featured' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="entry-media">
				<a class="entry-media-link" href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail( 'greenlight-card', array( 'class' => 'entry-media-image', 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?>
				</a>
			</figure>
		<?php endif; ?>
		<header class="entry-header">
			<p class="entry-meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php if ( $home_first_categories ) : ?>
					<span class="entry-taxonomy"><?php echo wp_kses_post( $home_first_categories ); ?></span>
				<?php endif; ?>
			</p>
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>
		</header>
		<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 36, '…' ) ); ?></p>
		<p class="entry-more">
			<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lire l’article', 'greenlight' ); ?></a>
		</p>
	</article>

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
					<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
					<p class="entry-more">
						<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lire l’article', 'greenlight' ); ?></a>
					</p>
				</article>
			</li>
		<?php endwhile; ?>
	</ul>
	<?php endif; ?>

	<?php the_posts_pagination(); ?>
<?php else : ?>
	<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
