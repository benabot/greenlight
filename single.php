<?php
/**
 * The single post template.
 *
 * @package Greenlight
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
			<header class="entry-header">
				<h1><?php the_title(); ?></h1>
				<p class="entry-meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
					<?php
					$author_id = get_the_author_meta( 'ID' );
					?>
					<span aria-hidden="true">—</span> <a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
						<?php echo esc_html( get_the_author() ); ?>
					</a>
				</p>
			</header>

			<section class="entry-content">
				<?php the_content(); ?>
			</section>

			<footer class="entry-footer">
				<?php
				$tags = get_the_tag_list( '', ', ' );

				if ( $tags ) :
					?>
					<p class="entry-tags"><?php echo wp_kses_post( $tags ); ?></p>
					<?php
				endif;

				wp_link_pages(
					array(
						'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Pages de l’article', 'greenlight' ) . '">',
						'after'  => '</nav>',
					)
				);
				?>
			</footer>
		</article>

		<?php
		the_post_navigation( array(
			'prev_text' => '← %title',
			'next_text' => '%title →',
		) );

		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
endif;

get_footer();
