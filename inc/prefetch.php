<?php
/**
 * DNS prefetch and preconnect for external domains.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs dns-prefetch and preconnect link tags in <head>.
 *
 * @return void
 */
function greenlight_output_prefetch() {
	$perf    = get_option( 'greenlight_performance_options', array() );
	$domains = isset( $perf['prefetch_domains'] ) ? trim( $perf['prefetch_domains'] ) : '';

	$domains = preg_split( '/[\s,]+/', $domains, -1, PREG_SPLIT_NO_EMPTY );
	$domains = array_filter( array_map( 'greenlight_normalize_prefetch_domain', (array) $domains ) );
	$domains = array_unique( $domains );
	$domains = apply_filters( 'greenlight_prefetch_domains', $domains );

	if ( ! is_array( $domains ) ) {
		$domains = array();
	}

	foreach ( $domains as $domain ) {
		$domain = esc_url( $domain );

		if ( '' === $domain ) {
			continue;
		}

		printf( '<link rel="dns-prefetch" href="%s">' . "\n", esc_attr( $domain ) );
		printf( '<link rel="preconnect" href="%s" crossorigin>' . "\n", esc_attr( $domain ) );
	}
}
add_action( 'wp_head', 'greenlight_output_prefetch', 2 );

/**
 * Normalizes a prefetch domain into an absolute URL.
 *
 * @param string $domain Domain or URL.
 *
 * @return string
 */
function greenlight_normalize_prefetch_domain( $domain ) {
	$domain = trim( wp_strip_all_tags( (string) $domain ) );

	if ( '' === $domain ) {
		return '';
	}

	if ( false === strpos( $domain, '://' ) ) {
		$domain = 'https://' . ltrim( $domain, '/' );
	}

	$parts = wp_parse_url( $domain );

	if ( empty( $parts['host'] ) ) {
		return '';
	}

	$scheme = ! empty( $parts['scheme'] ) ? $parts['scheme'] : 'https';
	$url    = $scheme . '://' . $parts['host'];

	if ( ! empty( $parts['port'] ) ) {
		$url .= ':' . absint( $parts['port'] );
	}

	return $url;
}
