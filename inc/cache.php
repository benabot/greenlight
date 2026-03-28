<?php
/**
 * Cache de page HTML pour le thème Greenlight.
 *
 * Enregistre la sortie HTML complète sur le disque et la sert directement
 * aux requêtes suivantes, court-circuitant WordPress tant que le fichier
 * est valide.
 *
 * Répertoire cache : WP_CONTENT_DIR/cache/greenlight/
 * Activation       : toggle Performance > enable_page_cache.
 * Durée de vie     : configurable via Performance > cache_lifetime.
 *
 * Exclusions automatiques :
 *   - admin / AJAX
 *   - requêtes non-GET
 *   - query string présente
 *   - utilisateurs connectés
 *   - prévisualisation / customizer
 *   - requêtes POST
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GREENLIGHT_CACHE_DIR', WP_CONTENT_DIR . '/cache/greenlight/' );

/* =========================================================
 * Helpers
 * ======================================================= */

/**
 * Retourne true si la mise en cache de page est activée et la requête est cacheable.
 *
 * @return bool
 */
function greenlight_cache_is_cacheable() {
	if ( is_admin() ) {
		return false;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
		return false;
	}

	// Exclure les requêtes avec query string (pagination Gutenberg, previews…)
	if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	if ( is_user_logged_in() ) {
		return false;
	}

	if ( function_exists( 'is_preview' ) && is_preview() ) {
		return false;
	}

	if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
		return false;
	}

	$perf = get_option( 'greenlight_performance_options', array() );

	return ! empty( $perf['enable_page_cache'] );
}

/**
 * Retourne le chemin du fichier cache pour la requête courante.
 *
 * @return string
 */
function greenlight_cache_file_path() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] )
		? wp_unslash( $_SERVER['REQUEST_URI'] )
		: '/';

	$slug = md5( home_url( $request_uri ) );

	return GREENLIGHT_CACHE_DIR . $slug . '.html';
}

/**
 * Retourne la durée de vie du cache en secondes.
 *
 * @return int
 */
function greenlight_cache_lifetime() {
	$perf     = get_option( 'greenlight_performance_options', array() );
	$lifetime = isset( $perf['cache_lifetime'] ) ? (int) $perf['cache_lifetime'] : 3600;
	$allowed  = array( 3600, 21600, 43200, 86400, 604800 );

	return in_array( $lifetime, $allowed, true ) ? $lifetime : 3600;
}

/* =========================================================
 * Mise en cache
 * ======================================================= */

/**
 * Démarre la capture du buffer de sortie pour la mise en cache.
 *
 * Si un fichier cache valide existe déjà, il est servi directement
 * et WordPress s'arrête là (exit).
 *
 * @return void
 */
function greenlight_cache_start() {
	if ( ! greenlight_cache_is_cacheable() ) {
		return;
	}

	$cache_file = greenlight_cache_file_path();
	$lifetime   = greenlight_cache_lifetime();

	// Servir depuis le cache si le fichier est encore frais.
	if ( file_exists( $cache_file ) && ( time() - filemtime( $cache_file ) ) < $lifetime ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$html = file_get_contents( $cache_file );

		if ( false !== $html ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}
	}

	ob_start( 'greenlight_cache_write' );
}
add_action( 'template_redirect', 'greenlight_cache_start', 1 );

/**
 * Callback du buffer de sortie : écrit le HTML sur le disque.
 *
 * @param string $html HTML complet de la page.
 * @return string Le même HTML (passthrough vers le navigateur).
 */
function greenlight_cache_write( $html ) {
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		return $html;
	}

	// Ne pas cacher les pages d'erreur ou de redirection.
	$status = http_response_code();
	if ( $status && 200 !== (int) $status ) {
		return $html;
	}

	if ( ! is_dir( GREENLIGHT_CACHE_DIR ) ) {
		wp_mkdir_p( GREENLIGHT_CACHE_DIR );
	}

	$cache_file = greenlight_cache_file_path();

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	file_put_contents( $cache_file, $html, LOCK_EX );

	return $html;
}

/* =========================================================
 * Purge
 * ======================================================= */

/**
 * Supprime tous les fichiers HTML du répertoire cache.
 *
 * @return void
 */
function greenlight_purge_page_cache() {
	if ( ! is_dir( GREENLIGHT_CACHE_DIR ) ) {
		return;
	}

	$files = glob( GREENLIGHT_CACHE_DIR . '*.html' );

	if ( ! $files ) {
		return;
	}

	foreach ( $files as $file ) {
		if ( is_file( $file ) ) {
			wp_delete_file( $file );
		}
	}
}

/**
 * Purge le cache à chaque modification de contenu.
 *
 * @return void
 */
function greenlight_purge_cache_on_content_change() {
	greenlight_purge_page_cache();
}
add_action( 'save_post',   'greenlight_purge_cache_on_content_change' );
add_action( 'delete_post', 'greenlight_purge_cache_on_content_change' );
add_action( 'switch_theme', 'greenlight_purge_page_cache' );

/* =========================================================
 * Headers HTTP
 * ======================================================= */

/**
 * Envoie des headers de cache navigateur pour les pages front-end.
 *
 * @return void
 */
function greenlight_send_cache_headers() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}

	$lifetime = greenlight_cache_lifetime();

	header( 'Cache-Control: public, max-age=' . $lifetime . ', stale-while-revalidate=60' );
	header( 'Vary: Accept-Encoding' );
}
add_action( 'send_headers', 'greenlight_send_cache_headers' );
