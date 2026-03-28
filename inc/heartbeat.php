<?php
/**
 * WordPress Heartbeat API control.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modifies the Heartbeat API interval based on settings.
 *
 * @param array $settings Heartbeat settings.
 * @return array
 */
function greenlight_heartbeat_settings( $settings ) {
	$perf = get_option( 'greenlight_performance_options', array() );

	$context = 'front';

	if ( is_admin() && function_exists( 'get_current_screen' ) ) {
		$screen  = get_current_screen();
		$context = ( $screen && 'post' === $screen->base ) ? 'editor' : 'admin';
	}

	$option_key = 'heartbeat_' . $context;
	$value      = isset( $perf[ $option_key ] ) ? $perf[ $option_key ] : 'default';

	if ( 'reduce' === $value ) {
		$settings['interval'] = 120;
	}

	return $settings;
}
add_filter( 'heartbeat_settings', 'greenlight_heartbeat_settings' );

/**
 * Disables the Heartbeat API entirely for a given context.
 *
 * @return void
 */
function greenlight_maybe_disable_heartbeat() {
	$perf = get_option( 'greenlight_performance_options', array() );

	if ( is_admin() ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'post' === $screen->base ) {
			$value = isset( $perf['heartbeat_editor'] ) ? $perf['heartbeat_editor'] : 'default';
		} else {
			$value = isset( $perf['heartbeat_admin'] ) ? $perf['heartbeat_admin'] : 'default';
		}
	} else {
		$value = isset( $perf['heartbeat_front'] ) ? $perf['heartbeat_front'] : 'default';
	}

	if ( 'disable' === $value ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'admin_enqueue_scripts', 'greenlight_maybe_disable_heartbeat', 99 );
add_action( 'wp_enqueue_scripts', 'greenlight_maybe_disable_heartbeat', 99 );
