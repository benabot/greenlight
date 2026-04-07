<?php
/**
 * Title: En-tête du site
 * Slug: greenlight/header
 * Categories: greenlight, header
 * Keywords: header, navigation, entete, logo
 * Block Types: core/template-part/header
 * Description: En-tête avec titre du site et navigation principale.
 * Template Types: header
 *
 * @package Greenlight
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"background","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--greenlight-main-gap, var(--wp--preset--spacing--md));padding-bottom:var(--greenlight-main-gap, var(--wp--preset--spacing--md));padding-left:var(--greenlight-main-gap, var(--wp--preset--spacing--md));padding-right:var(--greenlight-main-gap, var(--wp--preset--spacing--md))">

	<!-- wp:site-title {"level":0,"isLink":true,"style":{"typography":{"fontWeight":"700","letterSpacing":"-0.02em"}},"fontSize":"large"} /-->

	<!-- wp:navigation {"layout":{"type":"flex","flexWrap":"wrap"},"overlayMenu":"mobile","ariaLabel":"<?php echo esc_attr_x( 'Navigation principale', 'Pattern aria-label', 'greenlight' ); ?>"} /-->

</div>
<!-- /wp:group -->
