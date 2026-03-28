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

?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--lg);padding-bottom:var(--wp--preset--spacing--lg)">

	<!-- wp:heading {"textAlign":"center","style":{"typography":{"letterSpacing":"-0.02em"}},"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-text-align-center has-x-large-font-size" style="letter-spacing:-0.02em"><?php echo esc_html_x( 'Nous contacter', 'Pattern placeholder', 'greenlight' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
	<p class="has-text-align-center has-medium-font-size"><?php echo esc_html_x( 'Une question ? Envoyez-nous un message, nous répondons sous 24h.', 'Pattern placeholder', 'greenlight' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="greenlight_contact">
		<?php wp_nonce_field( 'greenlight_contact', 'greenlight_contact_nonce' ); ?>
		<p>
			<label for="contact-name"><?php echo esc_html_x( 'Nom', 'Pattern label', 'greenlight' ); ?></label>
			<input type="text" id="contact-name" name="contact_name" required autocomplete="name">
		</p>
		<p>
			<label for="contact-email"><?php echo esc_html_x( 'Email', 'Pattern label', 'greenlight' ); ?></label>
			<input type="email" id="contact-email" name="contact_email" required autocomplete="email">
		</p>
		<p>
			<label for="contact-message"><?php echo esc_html_x( 'Message', 'Pattern label', 'greenlight' ); ?></label>
			<textarea id="contact-message" name="contact_message" rows="5" required></textarea>
		</p>
		<p>
			<button type="submit"><?php echo esc_html_x( 'Envoyer', 'Pattern button', 'greenlight' ); ?></button>
		</p>
	</form>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
