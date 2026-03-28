<?php
/**
 * Title: Pied de page
 * Slug: greenlight/footer
 * Categories: greenlight, footer
 * Keywords: footer, pied de page, copyright, navigation
 * Block Types: core/template-part/footer
 * Description: Pied de page éditorial avec copyright, liens secondaires et badge Low Emission.
 * Template Types: footer
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"surface","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md)">

	<!-- wp:paragraph {"fontSize":"small","style":{"typography":{"letterSpacing":"0.05em","textTransform":"uppercase"}},"textColor":"on-surface-variant"} -->
	<p class="has-on-surface-variant-color has-text-color has-small-font-size" style="letter-spacing:0.05em;text-transform:uppercase">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <strong><?php echo esc_html( strtoupper( get_bloginfo( 'name' ) ) ); ?></strong>. <?php esc_html_e( 'DESIGNED FOR PERMANENCE.', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:navigation {"layout":{"type":"flex","flexWrap":"wrap"},"overlayMenu":"never","fontSize":"small","style":{"typography":{"letterSpacing":"0.05em","textTransform":"uppercase"}},"ariaLabel":"<?php echo esc_attr_x( 'Navigation secondaire', 'Pattern aria-label', 'greenlight' ); ?>"} /-->

	<!-- wp:paragraph {"fontSize":"small","style":{"typography":{"letterSpacing":"0.06em","textTransform":"uppercase"}},"textColor":"on-tertiary"} -->
	<p class="has-on-tertiary-color has-text-color has-small-font-size" style="letter-spacing:0.06em;text-transform:uppercase"><?php esc_html_e( '☘ LOW EMISSION MODE', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
