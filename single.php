<?php
/**
 * The single post template.
 *
 * @package Greenlight
 */

get_header();

$_gl_app  = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_show_author     = ! empty( $_gl_app['show_author'] );
$_gl_show_date       = ! empty( $_gl_app['show_date'] );
$_gl_show_tags       = ! empty( $_gl_app['show_tags'] );
$_gl_show_newsletter = ! empty( $_gl_app['show_newsletter_single'] );

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$single_cats = get_the_category();
		$single_cat  = $single_cats ? $single_cats[0] : null;
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
			<header class="entry-header">
				<p class="entry-badges">
					<?php if ( $single_cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $single_cat->term_id ) ); ?>" class="entry-category-pill"><?php echo esc_html( $single_cat->name ); ?></a>
					<?php endif; ?>
					<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( $_gl_show_author || $_gl_show_date ) : ?>
				<p class="entry-meta">
					<?php if ( $_gl_show_author ) : ?>
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="entry-author"><?php echo esc_html( get_the_author() ); ?></a>
					<?php endif; ?>
					<?php if ( $_gl_show_date ) : ?>
					<span class="entry-date">
						<?php esc_html_e( 'PUBLISHED', 'greenlight' ); ?>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
					</span>
					<?php endif; ?>
				</p>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-hero-media">
					<?php the_post_thumbnail( 'greenlight-hero', array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?>
					<?php if ( get_the_post_thumbnail_caption() ) : ?>
						<figcaption><?php echo wp_kses_post( get_the_post_thumbnail_caption() ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<p class="entry-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<section class="entry-content">
				<?php the_content(); ?>
			</section>

			<footer class="entry-footer">
				<?php
				$tags = $_gl_show_tags ? get_the_tags() : array();
				if ( $tags ) :
					?>
					<ul class="entry-tags" aria-label="<?php esc_attr_e( 'Tags', 'greenlight' ); ?>">
						<?php foreach ( $tags as $tag ) : ?>
							<li><a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag-pill"><?php echo esc_html( $tag->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<?php
				endif;

				wp_link_pages( array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Pages de l\'article', 'greenlight' ) . '">',
					'after'  => '</nav>',
				) );
				?>
			</footer>
		</article>

		<?php
		the_post_navigation( array(
			'prev_text' => '← %title',
			'next_text' => '%title →',
		) );
		?>

		<?php if ( $_gl_show_newsletter ) : ?>
		<section id="newsletter" class="newsletter-cta newsletter-cta--centered" aria-labelledby="newsletter-single-heading">
			<h2 id="newsletter-single-heading"><?php esc_html_e( 'Vous avez aimé cet article ?', 'greenlight' ); ?></h2>
			<p><?php esc_html_e( 'Recevez les prochains directement dans votre boîte.', 'greenlight' ); ?></p>
			<form class="newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="greenlight_newsletter">
				<?php wp_nonce_field( 'greenlight_newsletter', 'greenlight_newsletter_nonce' ); ?>
				<label for="newsletter-email-single" class="sr-only"><?php esc_html_e( 'Adresse email', 'greenlight' ); ?></label>
				<input type="email" id="newsletter-email-single" name="email" placeholder="<?php esc_attr_e( 'votre@email.com', 'greenlight' ); ?>" required>
				<button type="submit"><?php esc_html_e( "S'abonner", 'greenlight' ); ?></button>
			</form>
		</section>
		<?php endif; ?>

		<?php
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
endif;

get_footer();
