<?php
/**
 * The comments template.
 *
 * @package Greenlight
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" aria-labelledby="comments-heading">

	<?php if ( have_comments() ) : ?>
		<h2 id="comments-heading">
			<?php
			$comments_count = get_comments_number();
			printf(
				/* translators: %s: comment count */
				esc_html( _n( '%s commentaire', '%s commentaires', $comments_count, 'greenlight' ) ),
				esc_html( number_format_i18n( $comments_count ) )
			);
			?>
		</h2>

		<ol>
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>

	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply_before' => '<h2 id="reply-title">',
			'title_reply_after'  => '</h2>',
		)
	);
	?>

</section>
