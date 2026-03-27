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
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h1><?php the_title(); ?></h1>
				<p>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
					<?php
					$author_id = get_the_author_meta( 'ID' );
					?>
					— <a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
						<?php echo esc_html( get_the_author() ); ?>
					</a>
				</p>
			</header>

			<?php the_content(); ?>

			<footer>
				<?php
				the_tags( '<p>' . __( 'Tags :', 'greenlight' ) . ' ', ', ', '</p>' );
				wp_link_pages();
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
