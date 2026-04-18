<?php
/**
 * The footer template.
 *
 * @package Greenlight
 */

$_gl_app      = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_low_em   = ! empty( $_gl_app['show_low_emission'] );
$_gl_foot_nav = ! empty( $_gl_app['show_footer_nav'] );
$_gl_copy     = isset( $_gl_app['custom_copyright'] ) ? trim( $_gl_app['custom_copyright'] ) : '';
$_gl_badge    = greenlight_carbon_badge( 'footer' );
?>
</main>
<footer class="site-footer">
	<p class="footer-copy">
		<?php if ( '' !== $_gl_copy ) : ?>
			<?php echo esc_html( $_gl_copy ); ?>
		<?php else : ?>
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
			<strong><?php echo esc_html( strtoupper( get_bloginfo( 'name' ) ) ); ?></strong>.
			<?php esc_html_e( 'CONÇU POUR DURER.', 'greenlight' ); ?>
		<?php endif; ?>
		<?php if ( '' !== $_gl_badge ) : ?>
			<span class="footer-copy__badge"><?php echo $_gl_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
	</p>
	<?php if ( $_gl_foot_nav ) : ?>
	<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Navigation secondaire', 'greenlight' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>
	<?php endif; ?>
	<?php if ( $_gl_low_em ) : ?>
	<p class="footer-emission"><?php esc_html_e( '☘ MODE BASSE ÉMISSION', 'greenlight' ); ?></p>
	<?php endif; ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
