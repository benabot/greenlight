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
$_gl_app_h      = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_show_tagline = ! empty( $_gl_app_h['show_tagline'] );
?>
<header class="site-header">
	<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
	<?php if ( $_gl_show_tagline && get_bloginfo( 'description' ) ) : ?>
	<p class="site-tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	<?php endif; ?>
	<nav class="site-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'greenlight' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '<ul>%3$s</ul>',
			'fallback_cb'    => false,
		) );
		?>
	</nav>
	<a href="#newsletter" class="cta-subscribe"><?php esc_html_e( 'Subscribe', 'greenlight' ); ?></a>
</header>
<main id="main-content" class="site-main">
