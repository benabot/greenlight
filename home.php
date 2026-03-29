<?php
/**
 * The posts index template.
 *
 * @package Greenlight
 */

get_header();

$_gl_app           = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_show_thumbs   = ! empty( $_gl_app['show_thumbnails_archive'] );
$_gl_show_excerpts = ! empty( $_gl_app['show_excerpts_archive'] );

global $wp_query;

$posts_page_id = (int) get_option( 'page_for_posts' );
$home_title    = $posts_page_id ? get_the_title( $posts_page_id ) : get_bloginfo( 'name' );
$home_lead     = greenlight_get_archive_lead_text( 'home' );
$home_count    = (int) $wp_query->found_posts;
?>

<section class="archive-intro" aria-labelledby="home-heading">
	<div class="archive-intro-lead">
		<?php echo greenlight_carbon_badge( 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<h1 id="home-heading"><?php echo esc_html( $home_title ); ?></h1>
	</div>
	<div class="archive-intro-body">
		<p class="archive-lead"><?php echo esc_html( $home_lead ); ?></p>
		<p class="archive-count">
			<?php
			printf(
				/* translators: %s: number of posts in the blog index. */
				esc_html( _n( '%s article', '%s articles', $home_count, 'greenlight' ) ),
				esc_html( number_format_i18n( $home_count ) )
			);
			?>
		</p>
	</div>
</section>

<?php if ( have_posts() ) : ?>
	<?php
	the_post();
	$home_first_cats = get_the_category();
	$home_first_cat  = $home_first_cats ? $home_first_cats[0] : null;
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--featured' ); ?>>
		<?php if ( $_gl_show_thumbs && has_post_thumbnail() ) : ?>
			<figure class="entry-media">
				<a class="entry-media-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php
					the_post_thumbnail(
						'greenlight-card',
						array(
							'loading'       => 'eager',
							'decoding'      => 'async',
							'fetchpriority' => 'high',
						)
					);
					?>
				</a>
			</figure>
		<?php endif; ?>
		<div class="entry-body">
			<header class="entry-header">
				<p class="entry-label">
					<?php if ( $home_first_cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $home_first_cat->term_id ) ); ?>" class="entry-category"><?php echo esc_html( $home_first_cat->name ); ?></a>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
			</header>
			<?php if ( $_gl_show_excerpts ) : ?>
				<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 36, '…' ) ); ?></p>
			<?php endif; ?>
			<a href="<?php the_permalink(); ?>" class="entry-more"><?php esc_html_e( 'Read Manifesto →', 'greenlight' ); ?></a>
		</div>
	</article>

	<?php if ( have_posts() ) : ?>
	<ul class="post-list" aria-label="<?php esc_attr_e( 'Articles récents', 'greenlight' ); ?>">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php
			$teaser_cats = get_the_category();
			$teaser_cat  = $teaser_cats ? $teaser_cats[0] : null;
			?>
			<li class="post-item">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--teaser' ); ?>>
					<?php if ( $_gl_show_thumbs && has_post_thumbnail() ) : ?>
						<figure class="entry-media">
							<a class="entry-media-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php
								the_post_thumbnail(
									'greenlight-card',
									array(
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
								?>
							</a>
						</figure>
					<?php endif; ?>
					<div class="entry-body">
						<header class="entry-header">
							<p class="entry-label">
								<?php if ( $teaser_cat ) : ?>
									<a href="<?php echo esc_url( get_category_link( $teaser_cat->term_id ) ); ?>" class="entry-category"><?php echo esc_html( $teaser_cat->name ); ?></a>
								<?php endif; ?>
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							</p>
							<h2 class="entry-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
						</header>
						<?php if ( $_gl_show_excerpts ) : ?>
							<p class="entry-summary"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
						<?php endif; ?>
						<a href="<?php the_permalink(); ?>" class="entry-more"><?php esc_html_e( 'Read Manifesto →', 'greenlight' ); ?></a>
					</div>
				</article>
			</li>
		<?php endwhile; ?>
	</ul>
	<?php endif; ?>

	<?php the_posts_pagination(); ?>

	<section id="newsletter" class="newsletter-cta" aria-labelledby="newsletter-heading">
		<h2 id="newsletter-heading"><?php esc_html_e( 'Restez informé', 'greenlight' ); ?></h2>
		<p><?php esc_html_e( 'Recevez les prochains articles directement dans votre boîte.', 'greenlight' ); ?></p>
		<form class="newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="greenlight_newsletter">
			<?php wp_nonce_field( 'greenlight_newsletter', 'greenlight_newsletter_nonce' ); ?>
			<label for="newsletter-email-home" class="sr-only"><?php esc_html_e( 'Adresse email', 'greenlight' ); ?></label>
			<input type="email" id="newsletter-email-home" name="email" placeholder="<?php esc_attr_e( 'votre@email.com', 'greenlight' ); ?>" required>
			<button type="submit"><?php esc_html_e( "S'abonner", 'greenlight' ); ?></button>
		</form>
	</section>

<?php else : ?>
	<p><?php esc_html_e( 'Aucun contenu trouvé.', 'greenlight' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
