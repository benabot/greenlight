<?php
/**
 * Critical CSS inlining and main stylesheet deferral.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inlines critical CSS in <head> at highest priority.
 *
 * @return void
 */
function greenlight_inline_critical_css() {
	$perf = get_option( 'greenlight_performance_options', array() );

	if ( empty( $perf['enable_critical_css'] ) ) {
		return;
	}

	$critical_path = get_theme_file_path( 'assets/css/critical.css' );

	if ( ! file_exists( $critical_path ) ) {
		return;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$css = file_get_contents( $critical_path );

	if ( empty( $css ) ) {
		return;
	}

	echo '<style id="greenlight-critical-css">' . wp_strip_all_tags( $css ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'greenlight_inline_critical_css', 1 );

/**
 * Defers the main stylesheet by switching media to print with onload fallback.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Stylesheet handle.
 * @return string
 */
function greenlight_defer_main_css( $html, $handle ) {
	if ( 'greenlight-style' !== $handle ) {
		return $html;
	}

	$perf = get_option( 'greenlight_performance_options', array() );

	if ( empty( $perf['enable_critical_css'] ) ) {
		return $html;
	}

	// Replace media="all" with print + onload.
	$deferred = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
	$deferred = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $deferred );

	// Add noscript fallback.
	$noscript = '<noscript>' . $html . '</noscript>';

	return $deferred . $noscript;
}
add_filter( 'style_loader_tag', 'greenlight_defer_main_css', 10, 2 );
