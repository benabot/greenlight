<?php
/**
 * The 404 template.
 *
 * @package Greenlight
 */

get_header();
?>

<section aria-labelledby="error-heading">
	<h1 id="error-heading"><?php esc_html_e( 'Page introuvable', 'greenlight' ); ?></h1>
	<p><?php esc_html_e( 'La page que vous cherchez n\'existe pas ou a été déplacée.', 'greenlight' ); ?></p>
	<?php get_search_form(); ?>
</section>

<?php get_footer();
