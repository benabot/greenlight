<?php
/**
 * Bulk image optimization via AJAX.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for bulk image optimization.
 *
 * @return void
 */
function greenlight_bulk_optimize() {
	check_ajax_referer( 'greenlight_bulk_optimize', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'greenlight' ) ) );
	}

	$offset     = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 10;

	if ( ! in_array( $batch_size, array( 5, 10, 20 ), true ) ) {
		$batch_size = 10;
	}

	// Count total unoptimized images.
	$total_query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_greenlight_webp_done',
				'compare' => 'NOT EXISTS',
			),
			),
		)
	);

	$total = $total_query->found_posts;

	// Get batch.
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => $batch_size,
			'offset'         => $offset,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_greenlight_webp_done',
				'compare' => 'NOT EXISTS',
			),
			),
		)
	);

	$processed = 0;

	foreach ( $attachments as $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			update_post_meta( $attachment_id, '_greenlight_webp_done', 1 );
			++$processed;
			continue;
		}

		$source_type = wp_check_filetype( $file_path );

		if ( empty( $source_type['type'] ) || 0 !== strpos( $source_type['type'], 'image/' ) || 'image/webp' === $source_type['type'] || 'image/avif' === $source_type['type'] ) {
			update_post_meta( $attachment_id, '_greenlight_webp_done', 1 );
			++$processed;
			continue;
		}

		$original_size = (int) filesize( $file_path );

		// WebP conversion.
		if ( greenlight_is_webp_conversion_enabled() ) {
			$webp_path = greenlight_get_webp_sidecar_path( $file_path );

			if ( '' !== $webp_path && ! file_exists( $webp_path ) ) {
				$editor = wp_get_image_editor( $file_path );

				if ( ! is_wp_error( $editor ) ) {
					$editor->set_quality( greenlight_get_webp_quality() );
					$editor->save( $webp_path, 'image/webp' );
				}
			}
		}

		// AVIF conversion.
		if ( function_exists( 'greenlight_is_avif_conversion_enabled' ) && greenlight_is_avif_conversion_enabled() ) {
			$avif_path = greenlight_get_avif_sidecar_path( $file_path );

			if ( '' !== $avif_path && ! file_exists( $avif_path ) ) {
				$editor = wp_get_image_editor( $file_path );

				if ( ! is_wp_error( $editor ) ) {
					$avif_quality = greenlight_get_avif_quality();
					$editor->set_quality( $avif_quality );
					$editor->save( $avif_path, 'image/avif' );
				}
			}
		}

		// Record optimization meta.
		$optimized_size = 0;
		$webp_check     = greenlight_get_webp_sidecar_path( $file_path );

		if ( '' !== $webp_check && file_exists( $webp_check ) ) {
			$optimized_size = (int) filesize( $webp_check );
		}

		update_post_meta( $attachment_id, '_greenlight_webp_done', 1 );
		update_post_meta( $attachment_id, '_greenlight_original_size', $original_size );

		if ( $optimized_size > 0 ) {
			update_post_meta( $attachment_id, '_greenlight_optimized_size', $optimized_size );
		}

		++$processed;
	}

	$done = empty( $attachments ) || count( $attachments ) < $batch_size;

	wp_send_json_success(
		array(
			'processed' => $processed,
			'total'     => $total,
			'offset'    => $offset + $processed,
			'done'      => $done,
		)
	);
}
add_action( 'wp_ajax_greenlight_bulk_optimize', 'greenlight_bulk_optimize' );

/**
 * Filters media library to show only unoptimized attachments.
 *
 * @param array $query Query arguments.
 * @return array
 */
function greenlight_filter_unoptimized_attachments( $query ) {
	if ( ! isset( $_REQUEST['query']['greenlight_unoptimized'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $query;
	}

	$query['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'     => '_greenlight_webp_done',
			'compare' => 'NOT EXISTS',
		),
	);

	return $query;
}
add_filter( 'ajax_query_attachments_args', 'greenlight_filter_unoptimized_attachments' );

/**
 * Returns bulk optimization statistics.
 *
 * @return array{total: int, optimized: int, savings: int}
 */
function greenlight_get_bulk_stats() {
	global $wpdb;

	$total = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_type = 'attachment'
		AND post_status = 'inherit'
		AND post_mime_type LIKE 'image/%'"
	);

	$optimized = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_key = %s AND meta_value = %s",
			'_greenlight_webp_done',
			'1'
		)
	);

	$savings = 0;
	$rows    = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm1.meta_value AS original_size, pm2.meta_value AS optimized_size
			FROM {$wpdb->postmeta} pm1
			INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = %s
			WHERE pm1.meta_key = %s",
			'_greenlight_optimized_size',
			'_greenlight_original_size'
		)
	);

	foreach ( $rows as $row ) {
		$orig = (int) $row->original_size;
		$opt  = (int) $row->optimized_size;

		if ( $opt < $orig ) {
			$savings += $orig - $opt;
		}
	}

	return array(
		'total'     => $total,
		'optimized' => $optimized,
		'savings'   => $savings,
	);
}
