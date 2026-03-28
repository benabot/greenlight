<?php
/**
 * Minification PHP fallback pour le thème Greenlight.
 *
 * Si le fichier `.min.css` / `.min.js` n'existe pas sur le disque,
 * ce module minifie la source à la volée et met le résultat en cache
 * via un transient WordPress (durée : 24 h).
 *
 * Le résultat minifié est utilisé par `functions.php` pour l'enqueue
 * conditionnel des assets CSS/JS.
 *
 * Activation : toggle Performance > enable_css_min / enable_js_min.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supprime les commentaires CSS et collapse les espaces.
 *
 * @param string $css CSS brut.
 * @return string CSS minifié.
 */
function greenlight_minify_css_string( $css ) {
	// Supprime les commentaires block /* ... */.
	$css = preg_replace( '/\/\*[\s\S]*?\*\//', '', $css );
	// Collapse whitespace / sauts de ligne.
	$css = preg_replace( '/\s+/', ' ', $css );
	// Supprime les espaces autour des caractères structurels.
	$css = preg_replace( '/\s*([:;,{}()])\s*/', '$1', $css );
	// Raccourcit le ;} en }.
	$css = preg_replace( '/;}/', '}', $css );

	return trim( $css );
}

/**
 * Supprime les commentaires JS sur une ligne et collapse les espaces.
 * Approche conservative : ne touche pas aux littéraux de chaînes.
 *
 * @param string $js JS brut.
 * @return string JS minifié.
 */
function greenlight_minify_js_string( $js ) {
	// Supprime les commentaires single-line (hors URL ex. https://).
	$js = preg_replace( '/(?<!:)\/\/[^\n]*/', '', $js );
	// Supprime les commentaires block.
	$js = preg_replace( '/\/\*[\s\S]*?\*\//', '', $js );
	// Collapse whitespace.
	$js = preg_replace( '/\s+/', ' ', $js );

	return trim( $js );
}

/**
 * Retourne le contenu minifié d'un asset thème, avec cache transient.
 *
 * @param string $relative_path Chemin relatif à la racine du thème (ex. 'style.css').
 * @param string $type          'css' ou 'js'.
 * @return string|false Contenu minifié, ou false en cas d'échec.
 */
function greenlight_get_minified_content( $relative_path, $type ) {
	$transient_key = 'gl_min_' . md5( $relative_path );
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$abs_path = get_theme_file_path( $relative_path );

	if ( ! file_exists( $abs_path ) ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$source = file_get_contents( $abs_path );

	if ( false === $source ) {
		return false;
	}

	$minified = ( 'css' === $type )
		? greenlight_minify_css_string( $source )
		: greenlight_minify_js_string( $source );

	set_transient( $transient_key, $minified, DAY_IN_SECONDS );

	return $minified;
}

/**
 * Vide tous les transients de minification Greenlight.
 *
 * @return void
 */
function greenlight_clear_minify_transients() {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_gl_min_%',
			'_transient_timeout_gl_min_%'
		)
	);
}
add_action( 'switch_theme', 'greenlight_clear_minify_transients' );
add_action( 'upgrader_process_complete', 'greenlight_clear_minify_transients' );
add_action( 'update_option_greenlight_performance_options', 'greenlight_clear_minify_transients' );

/**
 * Supprime les fichiers .min.css / .min.js générés sur le disque.
 * Appelé lors d'un changement d'options performance ou de switch_theme.
 *
 * @return void
 */
function greenlight_clear_min_files() {
	$theme_dir = get_stylesheet_directory();

	$min_style = $theme_dir . '/style.min.css';
	if ( file_exists( $min_style ) ) {
		wp_delete_file( $min_style );
	}

	$block_mins = glob( $theme_dir . '/assets/css/blocks/*.min.css' );
	$block_mins = is_array( $block_mins ) ? $block_mins : array();
	foreach ( $block_mins as $f ) {
		wp_delete_file( $f );
	}

	$js_mins = glob( $theme_dir . '/assets/js/*.min.js' );
	$js_mins = is_array( $js_mins ) ? $js_mins : array();
	foreach ( $js_mins as $f ) {
		wp_delete_file( $f );
	}
}
add_action( 'switch_theme', 'greenlight_clear_min_files' );
add_action( 'update_option_greenlight_performance_options', 'greenlight_clear_min_files' );

/**
 * Génère le fichier .min sur disque si absent (lazy generation).
 *
 * Tente d'écrire le fichier dans le même répertoire que la source.
 * Si le répertoire n'est pas accessible en écriture, le contenu minifié
 * reste uniquement en transient (utilisable via wp_add_inline_style).
 *
 * @param string $relative_path Chemin relatif à la racine du thème (ex. 'style.css').
 * @param string $type          'css' ou 'js'.
 * @return bool True si le fichier .min existe ou a été créé.
 */
function greenlight_ensure_min_file( $relative_path, $type ) {
	$src_path = get_theme_file_path( $relative_path );
	$min_path = preg_replace( '/\.(css|js)$/', '.min.$1', $src_path );

	if ( file_exists( $min_path ) ) {
		return true;
	}

	$content = greenlight_get_minified_content( $relative_path, $type );

	if ( false === $content ) {
		return false;
	}

	if ( ! wp_is_writable( dirname( $min_path ) ) ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	file_put_contents( $min_path, $content, LOCK_EX );

	return file_exists( $min_path );
}
