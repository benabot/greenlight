<?php
/**
 * The archive template.
 *
 * @package Greenlight
 */

get_header();

global $wp_query;

$archive_count = (int) $wp_query->found_posts;
$archive_title = get_the_archive_title();
$archive_lead  = greenlight_get_archive_lead_text();
$archive_desc  = get_the_archive_description();
?>

<section class="archive-intro" aria-labelledby="archive-heading">
	<div class="archive-intro-lead">
		<p class="eyebrow"><?php esc_html_e( 'ARCHIVE DIGITALE', 'greenlight' ); ?></p>
		<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<h1 id="archive-heading"><?php echo wp_kses_post( $archive_title ); ?></h1>
	</div>
	<div class="archive-intro-body">
		<p class="archive-lead"><?php echo esc_html( $archive_lead ); ?></p>
		<?php if ( $archive_desc ) : ?>
			<p class="archive-note"><?php echo wp_kses_post( $archive_desc ); ?></p>
		<?php endif; ?>
		<p class="archive-count">
			<?php
			printf(
				esc_html( _n( '%s article', '%s articles', $archive_count, 'greenlight' ) ),
				esc_html( number_format_i18n( $archive_count ) )
			);
			?>
		</p>
	</div>
</section>

<?php if ( have_posts() ) : ?>
	<?php
	the_post();
	$archive_first_cats = get_the_category();
	$archive_first_cat  = $archive_first_cats ? $archive_first_cats[0] : null;
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--featured' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="entry-media">
				<a class="entry-media-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php the_post_thumbnail( 'greenlight-card', array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?>
				</a>
			</figure>
		<?php endif; ?>
		<div class="entry-body">
			<header class="entry-header">
				<p class="entry-label">
					<?php if ( $archive_first_cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $archive_first_cat->term_id ) ); ?>" class="entry-category"><?php echo esc_html( $archive_first_cat->name ); ?></a>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
			</header>
			<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 36, '…' ) ); ?></p>
			<a href="<?php the_permalink(); ?>" class="entry-more"><?php esc_html_e( 'Read Manifesto →', 'greenlight' ); ?></a>
		</div>
	</article>

	<?php if ( have_posts() ) : ?>
	<ul class="post-list post-list--grid" aria-label="<?php esc_attr_e( 'Articles de l\'archive', 'greenlight' ); ?>">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php
			$grid_cats = get_the_category();
			$grid_cat  = $grid_cats ? $grid_cats[0] : null;
			?>
			<li class="post-item">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--teaser' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="entry-media">
							<a class="entry-media-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php the_post_thumbnail( 'greenlight-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							</a>
						</figure>
					<?php endif; ?>
					<div class="entry-body">
						<header class="entry-header">
							<p class="entry-label">
								<?php if ( $grid_cat ) : ?>
									<a href="<?php echo esc_url( get_category_link( $grid_cat->term_id ) ); ?>" class="entry-category"><?php echo esc_html( $grid_cat->name ); ?></a>
								<?php endif; ?>
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							</p>
							<h2 class="entry-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
						</header>
						<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
						<a href="<?php the_permalink(); ?>" class="entry-more"><?php esc_html_e( 'Lire l\'article', 'greenlight' ); ?></a>
					</div>
				</article>
			</li>
		<?php endwhile; ?>
	</ul>
	<?php endif; ?>

	<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'greenlight' ); ?>">
		<?php
		the_posts_pagination( array(
			'prev_text'          => '&larr; ' . esc_html__( 'PRÉCÉDENT', 'greenlight' ),
			'next_text'          => esc_html__( 'SUIVANT', 'greenlight' ) . ' &rarr;',
			'screen_reader_text' => esc_html__( 'Navigation entre les pages', 'greenlight' ),
		) );
		?>
	</nav>

<?php else : ?>
	<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
<?php endif; ?>

<?php get_footer();
