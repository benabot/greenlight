<?php
/**
 * The footer template.
 *
 * @package Greenlight
 */

?>
</main>
<footer class="site-footer">
	<p class="footer-copy">
		&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
		<strong><?php echo esc_html( strtoupper( get_bloginfo( 'name' ) ) ); ?></strong>.
		<?php esc_html_e( 'DESIGNED FOR PERMANENCE.', 'greenlight' ); ?>
	</p>
	<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Navigation secondaire', 'greenlight' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'footer',
			'container'      => false,
			'items_wrap'     => '<ul>%3$s</ul>',
			'fallback_cb'    => false,
		) );
		?>
	</nav>
	<p class="footer-emission"><?php esc_html_e( '☘ LOW EMISSION MODE', 'greenlight' ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
