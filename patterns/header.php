<?php
/**
 * Title: En-tête du site
 * Slug: greenlight/header
 * Categories: greenlight, header
 * Keywords: header, navigation, entete, logo
 * Block Types: core/template-part/header
 * Description: En-tête avec titre du site et navigation principale.
 * Template Types: header
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|sm","bottom":"var:preset|spacing|sm","left":"var:preset|spacing|sm","right":"var:preset|spacing|sm"}}},"backgroundColor":"background","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--sm);padding-bottom:var(--wp--preset--spacing--sm);padding-left:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--sm)">

	<!-- wp:site-title {"level":0,"isLink":true,"style":{"typography":{"fontWeight":"700","letterSpacing":"-0.02em"}},"fontSize":"large"} /-->

	<!-- wp:navigation {"layout":{"type":"flex","flexWrap":"wrap"},"overlayMenu":"mobile","ariaLabel":"<?php echo esc_attr_x( 'Navigation principale', 'Pattern aria-label', 'greenlight' ); ?>"} /-->

</div>
<!-- /wp:group -->
