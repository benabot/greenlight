<?php
/**
 * Build asset cleanup helpers for Greenlight.
 *
 * The theme no longer generates minified assets lazily on front requests.
 * Minified files are expected to be produced during deployment, then served
 * when present.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes generated .min.css / .min.js files from disk.
 *
 * @return void
 */
function greenlight_clear_min_files() {
	$theme_dir = get_stylesheet_directory();
	$bundle    = $theme_dir . '/assets/css/greenlight-bundle.css';

	if ( file_exists( $bundle ) ) {
		wp_delete_file( $bundle );
	}

	$min_style = $theme_dir . '/style.min.css';
	if ( file_exists( $min_style ) ) {
		wp_delete_file( $min_style );
	}

	$block_mins = glob( $theme_dir . '/assets/css/blocks/*.min.css' );
	$block_mins = is_array( $block_mins ) ? $block_mins : array();
	foreach ( $block_mins as $file_path ) {
		wp_delete_file( $file_path );
	}

	$js_mins = glob( $theme_dir . '/assets/js/*.min.js' );
	$js_mins = is_array( $js_mins ) ? $js_mins : array();
	foreach ( $js_mins as $file_path ) {
		wp_delete_file( $file_path );
	}
}
add_action( 'switch_theme', 'greenlight_clear_min_files' );
