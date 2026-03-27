<?php
/**
 * Title: Héro
 * Slug: greenlight/hero
 * Categories: greenlight
 * Keywords: hero, banner, titre, accueil
 * Block Types: core/group
 * Description: Section héro pleine largeur avec titre, description et bouton CTA.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|sm","right":"var:preset|spacing|sm"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--sm)">

	<!-- wp:heading {"level":1,"style":{"typography":{"letterSpacing":"-0.03em","lineHeight":"1.1"}},"textColor":"text","fontSize":"xx-large"} -->
	<h1 class="wp-block-heading has-text-color has-text-text-color has-xx-large-font-size" style="letter-spacing:-0.03em;line-height:1.1"><?php echo esc_html_x( 'Titre principal mémorable', 'Pattern placeholder', 'greenlight' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|sm"}}},"textColor":"text","fontSize":"large"} -->
	<p class="has-text-color has-text-text-color has-large-font-size" style="margin-top:var(--wp--preset--spacing--sm)"><?php echo esc_html_x( 'Une description concise qui donne envie d\'en savoir plus. Une ou deux phrases suffisent.', 'Pattern placeholder', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|md"}}}} -->
	<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--md)">
		<!-- wp:button {"backgroundColor":"primary","textColor":"background"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-primary-background-color has-text-color has-background wp-element-button"><?php echo esc_html_x( 'Découvrir', 'Pattern placeholder', 'greenlight' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'En savoir plus', 'Pattern placeholder', 'greenlight' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
