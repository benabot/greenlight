<?php
/**
 * SEO 301/302 redirects manager and 404 logger.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles redirect rules on template_redirect.
 *
 * @return void
 */
function greenlight_handle_redirects() {
	if ( is_admin() ) {
		return;
	}

	$redirects = get_option( 'greenlight_redirects', array() );

	if ( empty( $redirects ) || ! is_array( $redirects ) ) {
		return;
	}

	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $request_path ) || '' === $request_path ) {
		return;
	}

	$request_path = untrailingslashit( $request_path );

	foreach ( $redirects as $index => $rule ) {
		$source = isset( $rule['source'] ) ? untrailingslashit( $rule['source'] ) : '';

		if ( '' === $source || $source !== $request_path ) {
			continue;
		}

		$destination = isset( $rule['destination'] ) ? $rule['destination'] : '';
		$code        = isset( $rule['code'] ) ? (int) $rule['code'] : 301;

		if ( '' === $destination ) {
			continue;
		}

		if ( ! in_array( $code, array( 301, 302 ), true ) ) {
			$code = 301;
		}

		// Increment hits counter.
		$redirects[ $index ]['hits'] = isset( $rule['hits'] ) ? (int) $rule['hits'] + 1 : 1;
		update_option( 'greenlight_redirects', $redirects, false );

		wp_safe_redirect( esc_url_raw( $destination ), $code );
		exit;
	}
}
add_action( 'template_redirect', 'greenlight_handle_redirects' );

/**
 * Logs 404 errors.
 *
 * @return void
 */
function greenlight_log_404() {
	if ( ! is_404() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	if ( '' === $request_uri ) {
		return;
	}

	$log = get_option( 'greenlight_404_log', array() );

	if ( ! is_array( $log ) ) {
		$log = array();
	}

	// Add entry at the beginning.
	array_unshift(
		$log,
		array(
			'url'  => $request_uri,
			'time' => current_time( 'mysql' ),
			'ip'   => wp_privacy_anonymize_ip( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ),
		)
	);

	// Keep only last 50 entries.
	$log = array_slice( $log, 0, 50 );

	update_option( 'greenlight_404_log', $log, false );
}
add_action( 'wp', 'greenlight_log_404' );

/**
 * Handles adding a new redirect via admin_post.
 *
 * @return void
 */
function greenlight_handle_add_redirect() {
	check_admin_referer( 'greenlight_add_redirect' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$source      = isset( $_POST['redirect_source'] ) ? sanitize_text_field( wp_unslash( $_POST['redirect_source'] ) ) : '';
	$destination = isset( $_POST['redirect_destination'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_destination'] ) ) : '';
	$code        = isset( $_POST['redirect_code'] ) ? (int) $_POST['redirect_code'] : 301;

	// Forcer un chemin relatif (commence par /).
	if ( '' !== $source && '/' !== $source[0] ) {
		$source = '/' . $source;
	}

	if ( '' === $source || '' === $destination ) {
		wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_error=1' ) );
		exit;
	}

	if ( ! in_array( $code, array( 301, 302 ), true ) ) {
		$code = 301;
	}

	$redirects = get_option( 'greenlight_redirects', array() );

	if ( ! is_array( $redirects ) ) {
		$redirects = array();
	}

	$redirects[] = array(
		'source'      => $source,
		'destination' => $destination,
		'code'        => $code,
		'hits'        => 0,
		'created_at'  => current_time( 'mysql' ),
	);

	update_option( 'greenlight_redirects', $redirects, false );

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_added=1' ) );
	exit;
}
add_action( 'admin_post_greenlight_add_redirect', 'greenlight_handle_add_redirect' );

/**
 * Handles deleting a redirect via admin_post.
 *
 * @return void
 */
function greenlight_handle_delete_redirect() {
	check_admin_referer( 'greenlight_delete_redirect' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$index     = isset( $_POST['redirect_index'] ) ? (int) $_POST['redirect_index'] : -1;
	$redirects = get_option( 'greenlight_redirects', array() );

	if ( ! is_array( $redirects ) ) {
		$redirects = array();
	}

	if ( isset( $redirects[ $index ] ) ) {
		array_splice( $redirects, $index, 1 );
		update_option( 'greenlight_redirects', $redirects, false );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_deleted=1' ) );
	exit;
}
add_action( 'admin_post_greenlight_delete_redirect', 'greenlight_handle_delete_redirect' );

/**
 * Handles CSV import of redirects via admin_post.
 *
 * @return void
 */
function greenlight_handle_import_redirects() {
	check_admin_referer( 'greenlight_import_redirects' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	if ( empty( $_FILES['redirects_csv']['tmp_name'] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_error=1' ) );
		exit;
	}

	$tmp_file = sanitize_text_field( $_FILES['redirects_csv']['tmp_name'] );

	// Vérifier que le fichier provient bien d'un upload HTTP.
	if ( ! is_uploaded_file( $tmp_file ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_error=1' ) );
		exit;
	}

	// Limiter la taille du fichier CSV (256 Ko max).
	if ( filesize( $tmp_file ) > 256 * 1024 ) {
		wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_error=1' ) );
		exit;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$csv_content = file_get_contents( $tmp_file );

	if ( false === $csv_content ) {
		wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirect_error=1' ) );
		exit;
	}

	$redirects = get_option( 'greenlight_redirects', array() );

	if ( ! is_array( $redirects ) ) {
		$redirects = array();
	}

	$lines       = explode( "\n", $csv_content );
	$imported    = 0;
	$max_imports = 500;

	foreach ( $lines as $line ) {
		if ( $imported >= $max_imports ) {
			break;
		}

		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = str_getcsv( $line );

		if ( count( $parts ) < 2 ) {
			continue;
		}

		$source      = sanitize_text_field( trim( $parts[0] ) );
		$destination = esc_url_raw( trim( $parts[1] ) );
		$code        = isset( $parts[2] ) ? (int) trim( $parts[2] ) : 301;

		// Forcer un chemin relatif pour la source.
		if ( '' !== $source && '/' !== $source[0] ) {
			$source = '/' . $source;
		}

		if ( '' === $source || '' === $destination ) {
			continue;
		}

		if ( ! in_array( $code, array( 301, 302 ), true ) ) {
			$code = 301;
		}

		$redirects[] = array(
			'source'      => $source,
			'destination' => $destination,
			'code'        => $code,
			'hits'        => 0,
			'created_at'  => current_time( 'mysql' ),
		);

		++$imported;
	}

	update_option( 'greenlight_redirects', $redirects, false );

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=seo&redirects_imported=' . $imported ) );
	exit;
}
add_action( 'admin_post_greenlight_import_redirects', 'greenlight_handle_import_redirects' );
