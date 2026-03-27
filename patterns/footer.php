<?php
/**
 * Title: Pied de page
 * Slug: greenlight/footer
 * Categories: greenlight, footer
 * Keywords: footer, pied de page, copyright, navigation
 * Block Types: core/template-part/footer
 * Description: Pied de page avec copyright et liens de navigation.
 * Template Types: footer
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|sm","right":"var:preset|spacing|sm"}}},"backgroundColor":"surface","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--sm)">

	<!-- wp:paragraph {"fontSize":"small","style":{"color":{"text":"var:preset|color|text"}}} -->
	<p class="has-small-font-size">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong></p>
	<!-- /wp:paragraph -->

	<!-- wp:navigation {"layout":{"type":"flex","flexWrap":"wrap"},"overlayMenu":"never","fontSize":"small","ariaLabel":"<?php echo esc_attr_x( 'Navigation secondaire', 'Pattern aria-label', 'greenlight' ); ?>"} /-->

</div>
<!-- /wp:group -->
