<?php
/**
 * Custom robots.txt management.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the default robots.txt content.
 *
 * @return string
 */
function greenlight_get_default_robots_txt() {
	$sitemap_url = home_url( '/wp-sitemap.xml' );

	return "User-agent: *\nAllow: /\n\nSitemap: " . $sitemap_url . "\n";
}

/**
 * Returns the robots.txt content that should be served publicly.
 *
 * @return string
 */
function greenlight_get_robots_txt_content() {
	$seo_options = greenlight_get_seo_options();

	if ( ! empty( $seo_options['custom_robots_txt'] ) ) {
		return (string) $seo_options['custom_robots_txt'];
	}

	return greenlight_get_default_robots_txt();
}

/**
 * Filters the robots.txt output with custom content if defined.
 *
 * @param string $output  Default robots.txt output.
 * @param bool   $_is_public Whether the site is public.
 * @return string
 */
function greenlight_custom_robots_txt( $output, $_is_public ) {
	unset( $_is_public );

	return '' !== trim( greenlight_get_robots_txt_content() ) ? greenlight_get_robots_txt_content() : $output;
}
add_filter( 'robots_txt', 'greenlight_custom_robots_txt', 10, 2 );

/**
 * Serves robots.txt as plain text for direct requests on subdirectory installs.
 *
 * @return void
 */
function greenlight_serve_robots_txt() {
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$request_path = '' !== $request_uri ? (string) wp_parse_url( $request_uri, PHP_URL_PATH ) : '';

	if ( '' === $request_path || false === strpos( $request_path, 'robots.txt' ) ) {
		return;
	}

	nocache_headers();
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	echo esc_textarea( greenlight_get_robots_txt_content() );
	exit;
}
add_action( 'template_redirect', 'greenlight_serve_robots_txt', 0 );
