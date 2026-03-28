<?php
/**
 * Title: Grille de cartes
 * Slug: greenlight/cards
 * Categories: greenlight
 * Keywords: cartes, grille, articles, liste
 * Block Types: core/columns
 * Description: Grille de trois cartes responsive en flexbox.
 *
 * @package Greenlight
 */

?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--lg);padding-bottom:var(--wp--preset--spacing--lg)">

	<!-- wp:heading {"textAlign":"left","style":{"typography":{"letterSpacing":"-0.02em"}},"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-x-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Nos services', 'Pattern placeholder', 'greenlight' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"isStackedOnMobile":true,"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|md","left":"var:preset|spacing|md"}}}} -->
	<div class="wp-block-columns is-not-stacked-on-mobile">

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"is-style-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"flow"}} -->
			<div class="wp-block-group is-style-card" style="padding:var(--wp--preset--spacing--md)">
				<!-- wp:heading {"level":3,"fontSize":"large","style":{"typography":{"letterSpacing":"-0.02em"}}} -->
				<h3 class="wp-block-heading has-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Titre carte 1', 'Pattern placeholder', 'greenlight' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"fontSize":"medium"} -->
				<p class="has-medium-font-size"><?php echo esc_html_x( 'Description courte et percutante de ce service ou contenu.', 'Pattern placeholder', 'greenlight' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph -->
				<p><a href="#"><?php echo esc_html_x( 'Lire la suite →', 'Pattern placeholder', 'greenlight' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"is-style-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"flow"}} -->
			<div class="wp-block-group is-style-card" style="padding:var(--wp--preset--spacing--md)">
				<!-- wp:heading {"level":3,"fontSize":"large","style":{"typography":{"letterSpacing":"-0.02em"}}} -->
				<h3 class="wp-block-heading has-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Titre carte 2', 'Pattern placeholder', 'greenlight' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"fontSize":"medium"} -->
				<p class="has-medium-font-size"><?php echo esc_html_x( 'Description courte et percutante de ce service ou contenu.', 'Pattern placeholder', 'greenlight' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph -->
				<p><a href="#"><?php echo esc_html_x( 'Lire la suite →', 'Pattern placeholder', 'greenlight' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"is-style-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"flow"}} -->
			<div class="wp-block-group is-style-card" style="padding:var(--wp--preset--spacing--md)">
				<!-- wp:heading {"level":3,"fontSize":"large","style":{"typography":{"letterSpacing":"-0.02em"}}} -->
				<h3 class="wp-block-heading has-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Titre carte 3', 'Pattern placeholder', 'greenlight' ); ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"fontSize":"medium"} -->
				<p class="has-medium-font-size"><?php echo esc_html_x( 'Description courte et percutante de ce service ou contenu.', 'Pattern placeholder', 'greenlight' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph -->
				<p><a href="#"><?php echo esc_html_x( 'Lire la suite →', 'Pattern placeholder', 'greenlight' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
