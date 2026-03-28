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
 * Generates the concatenated CSS bundle file.
 *
 * @return bool True on success.
 */
function greenlight_generate_bundle() {
	$theme_dir   = get_stylesheet_directory();
	$bundle_path = $theme_dir . '/assets/css/greenlight-bundle.css';

	$files = array( $theme_dir . '/style.css' );

	$block_files = glob( $theme_dir . '/assets/css/blocks/*.css' );

	if ( $block_files ) {
		foreach ( $block_files as $file ) {
			// Skip .min.css files.
			if ( preg_match( '/\.min\.css$/', $file ) ) {
				continue;
			}
			$files[] = $file;
		}
	}

	$css = '';

	foreach ( $files as $file ) {
		if ( ! file_exists( $file ) ) {
			continue;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );

		if ( false !== $content ) {
			$css .= '/* ' . basename( $file ) . " */\n" . $content . "\n\n";
		}
	}

	if ( '' === $css ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	return false !== file_put_contents( $bundle_path, $css );
}

/**
 * Invalidates the bundle when performance options change.
 *
 * @return void
 */
function greenlight_invalidate_bundle() {
	$bundle_path = get_stylesheet_directory() . '/assets/css/greenlight-bundle.css';

	if ( file_exists( $bundle_path ) ) {
		wp_delete_file( $bundle_path );
	}
}
add_action( 'update_option_greenlight_performance_options', 'greenlight_invalidate_bundle' );
add_action( 'switch_theme', 'greenlight_invalidate_bundle' );

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

	$theme_dir   = get_stylesheet_directory();
	$bundle_path = $theme_dir . '/assets/css/greenlight-bundle.css';

	// Auto-generate if missing.
	if ( ! file_exists( $bundle_path ) ) {
		greenlight_generate_bundle();
	}

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
