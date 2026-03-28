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
 * Filters the robots.txt output with custom content if defined.
 *
 * @param string $output  Default robots.txt output.
 * @param bool   $_is_public Whether the site is public.
 * @return string
 */
function greenlight_custom_robots_txt( $output, $_is_public ) {
	unset( $_is_public );

	$seo_options = greenlight_get_seo_options();

	if ( ! empty( $seo_options['custom_robots_txt'] ) ) {
		return $seo_options['custom_robots_txt'];
	}

	return $output;
}
add_filter( 'robots_txt', 'greenlight_custom_robots_txt', 10, 2 );
