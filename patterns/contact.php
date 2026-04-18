<?php
/**
 * Title: Section contact
 * Slug: greenlight/contact
 * Categories: greenlight
 * Keywords: contact, formulaire, message
 * Block Types: core/group
 * Description: Section contact avec titre et formulaire HTML natif.
 *
 * @package Greenlight
 */

$greenlight_contact_email = sanitize_email( (string) get_option( 'admin_email', '' ) );
$greenlight_contact_href  = '';
$greenlight_contact_label = '';

if ( is_email( $greenlight_contact_email ) ) {
	$greenlight_contact_href  = 'mailto:' . antispambot( $greenlight_contact_email );
	$greenlight_contact_label = antispambot( $greenlight_contact_email );
}

?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--lg);padding-bottom:var(--wp--preset--spacing--lg)">

	<!-- wp:heading {"textAlign":"center","style":{"typography":{"letterSpacing":"-0.02em"}},"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-text-align-center has-x-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Nous contacter', 'Pattern placeholder', 'greenlight' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
	<p class="has-text-align-center has-medium-font-size"><?php echo esc_html_x( 'Une question ? Envoyez-nous un message, nous répondons sous 24h.', 'Pattern placeholder', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><?php echo esc_html_x( 'Le thème ne fournit pas de formulaire public natif. Utilisez une adresse de contact dédiée ou un plugin de formulaire si un traitement serveur est requis.', 'Pattern note', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

	<?php if ( '' !== $greenlight_contact_href && '' !== $greenlight_contact_label ) : ?>
	<!-- wp:paragraph {"align":"center","fontSize":"small"} -->
	<p class="has-text-align-center has-small-font-size">
		<a href="<?php echo esc_url( $greenlight_contact_href ); ?>"><?php echo esc_html( $greenlight_contact_label ); ?></a>
	</p>
	<!-- /wp:paragraph -->
	<?php endif; ?>

</div>
<!-- /wp:group -->
