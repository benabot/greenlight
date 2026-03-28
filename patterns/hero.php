<?php
/**
 * Title: Héro
 * Slug: greenlight/hero
 * Categories: greenlight
 * Keywords: hero, banner, titre, accueil
 * Block Types: core/group
 * Description: Section héro asymétrique avec Carbon Badge, titre surdimensionné et CTA.
 *
 * @package Greenlight
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|sm","right":"var:preset|spacing|sm"}}},"backgroundColor":"background","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"bottom","justifyContent":"space-between"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--sm)">

	<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"60%"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
	<div class="wp-block-group">

		<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|sm"}}},"textColor":"on-tertiary","backgroundColor":"tertiary","className":"carbon-badge"} -->
		<p class="has-on-tertiary-color has-tertiary-background-color has-text-color has-background carbon-badge" style="margin-bottom:var(--wp--preset--spacing--sm)"><?php echo esc_html_x( '0.2g CO₂/vue', 'Pattern placeholder', 'greenlight' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"style":{"typography":{"letterSpacing":"-0.03em","lineHeight":"1.1"}},"textColor":"text","fontSize":"xx-large"} -->
		<h1 class="wp-block-heading has-text-color has-text-text-color has-xx-large-font-size" style="letter-spacing:-0.03em;line-height:1.1;max-width:14ch"><?php echo esc_html_x( 'Titre principal mémorable', 'Pattern placeholder', 'greenlight' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|md"}}}} -->
		<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--md)">
			<!-- wp:button {"backgroundColor":"primary","textColor":"surface-alt"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-surface-alt-color has-primary-background-color has-text-color has-background wp-element-button"><?php echo esc_html_x( 'Découvrir', 'Pattern placeholder', 'greenlight' ); ?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'En savoir plus', 'Pattern placeholder', 'greenlight' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"36%"}},"layout":{"type":"flex","orientation":"vertical","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group">

		<!-- wp:paragraph {"textColor":"on-surface-variant","fontSize":"large","style":{"typography":{"lineHeight":"1.55"}}} -->
		<p class="has-on-surface-variant-color has-text-color has-large-font-size" style="line-height:1.55;max-width:44ch"><?php echo esc_html_x( 'Une description concise qui donne envie d\'en savoir plus. Une ou deux phrases suffisent.', 'Pattern placeholder', 'greenlight' ); ?></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
