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

	// Merge manual domains with auto-detected ones.
	$manual    = array_filter( array_map( 'trim', explode( "\n", $domains ) ) );
	$detected  = get_transient( 'greenlight_detected_domains' );
	$all       = array_unique( array_merge( $manual, is_array( $detected ) ? $detected : array() ) );

	foreach ( $all as $domain ) {
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
 * Auto-detects external domains from recent post content.
 *
 * Scheduled via weekly cron.
 *
 * @return void
 */
function greenlight_detect_external_domains() {
	$posts = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'no_found_rows'  => true,
		'fields'         => 'ids',
	) );

	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$domains   = array();

	foreach ( $posts as $post_id ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( empty( $content ) ) {
			continue;
		}

		if ( preg_match_all( '#https?://([^/\s"\'<>]+)#i', $content, $matches ) ) {
			foreach ( $matches[1] as $host ) {
				$host = strtolower( $host );

				if ( $host !== $home_host ) {
					$domains[] = 'https://' . $host;
				}
			}
		}
	}

	$domains = array_unique( $domains );
	$domains = array_slice( $domains, 0, 20 );

	set_transient( 'greenlight_detected_domains', $domains, WEEK_IN_SECONDS );
}

/**
 * Schedules the weekly domain detection cron.
 *
 * @return void
 */
function greenlight_schedule_domain_detection() {
	if ( ! wp_next_scheduled( 'greenlight_detect_domains_cron' ) ) {
		wp_schedule_event( time(), 'weekly', 'greenlight_detect_domains_cron' );
	}
}
add_action( 'init', 'greenlight_schedule_domain_detection' );
add_action( 'greenlight_detect_domains_cron', 'greenlight_detect_external_domains' );

/**
 * Cleans up cron on theme switch.
 *
 * @return void
 */
function greenlight_clear_domain_detection_cron() {
	wp_clear_scheduled_hook( 'greenlight_detect_domains_cron' );
}
add_action( 'switch_theme', 'greenlight_clear_domain_detection_cron' );
