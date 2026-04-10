<?php
/**
 * The header template.
 *
 * @package Greenlight
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main-content" class="skip-link"><?php esc_html_e( 'Aller au contenu principal', 'greenlight' ); ?></a>
<?php
$_gl_app_h            = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_header_layout    = isset( $_gl_app_h['header_layout'] ) && in_array( $_gl_app_h['header_layout'], array( 'inline', 'split', 'stacked' ), true ) ? sanitize_key( (string) $_gl_app_h['header_layout'] ) : 'inline';
$_gl_header_sticky    = ! empty( $_gl_app_h['header_sticky'] );
$_gl_nav_case         = isset( $_gl_app_h['nav_link_case'] ) && 'uppercase' === $_gl_app_h['nav_link_case'] ? 'uppercase' : 'normal';
$_gl_submenu_style    = isset( $_gl_app_h['submenu_style'] ) && 'surface' === $_gl_app_h['submenu_style'] ? 'surface' : 'plain';
$_gl_show_tagline     = ! empty( $_gl_app_h['show_tagline'] );
$_gl_newsletter_on    = ! empty( $_gl_app_h['newsletter_enabled'] );
$_gl_newsletter_place = isset( $_gl_app_h['newsletter_placement'] ) ? $_gl_app_h['newsletter_placement'] : 'footer';
$_gl_show_cta         = $_gl_newsletter_on && in_array( $_gl_newsletter_place, array( 'header', 'both' ), true );
$_gl_nav_style        = isset( $_gl_app_h['nav_style'] ) && 'burger' === $_gl_app_h['nav_style'] ? 'burger' : 'inline';
?>
<header class="site-header site-header--layout-<?php echo esc_attr( $_gl_header_layout ); ?> site-header--nav-<?php echo esc_attr( $_gl_nav_case ); ?> site-header--submenu-<?php echo esc_attr( $_gl_submenu_style ); ?><?php echo $_gl_header_sticky ? ' site-header--sticky' : ''; ?>">
	<?php if ( 'burger' === $_gl_nav_style ) : ?>
	<input type="checkbox" id="nav-toggle" class="nav-toggle sr-only" aria-hidden="true">
	<?php endif; ?>
	<div class="site-branding">
		<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		<?php if ( $_gl_show_tagline && get_bloginfo( 'description' ) ) : ?>
		<p class="site-tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( 'burger' === $_gl_nav_style ) : ?>
	<label for="nav-toggle" class="nav-burger" aria-label="<?php esc_attr_e( 'Menu', 'greenlight' ); ?>">
		<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<rect y="4"  width="24" height="2" fill="currentColor"/>
			<rect y="11" width="24" height="2" fill="currentColor"/>
			<rect y="18" width="24" height="2" fill="currentColor"/>
		</svg>
	</label>
	<?php endif; ?>
	<nav class="site-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'greenlight' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>
	<?php if ( $_gl_show_cta ) : ?>
	<a href="#newsletter" class="cta-subscribe"><?php esc_html_e( 'Subscribe', 'greenlight' ); ?></a>
	<?php endif; ?>
</header>
