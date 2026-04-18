<?php
/**
 * CSS concatenation for performance.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the concatenated bundle instead of individual styles if enabled.
 *
 * @return void
 */
function greenlight_maybe_enqueue_bundle() {
	$perf = get_option( 'greenlight_performance_options', array() );

	if ( empty( $perf['enable_concat'] ) ) {
		return;
	}

	$theme_dir    = get_stylesheet_directory();
	$bundle_path  = $theme_dir . '/assets/css/greenlight-bundle.css';

	if ( ! file_exists( $bundle_path ) ) {
		return;
	}

	// Dequeue individual styles.
	wp_dequeue_style( 'greenlight-style' );

	$blocks = array( 'navigation', 'image', 'heading', 'paragraph', 'separator', 'button', 'group', 'query' );
	foreach ( $blocks as $block ) {
		wp_dequeue_style( 'greenlight-block-' . $block );
	}

	// Enqueue bundle.
	wp_enqueue_style(
		'greenlight-bundle',
		get_stylesheet_directory_uri() . '/assets/css/greenlight-bundle.css',
		array(),
		filemtime( $bundle_path )
	);
}
add_action( 'wp_enqueue_scripts', 'greenlight_maybe_enqueue_bundle', 20 );
