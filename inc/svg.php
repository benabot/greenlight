<?php
/**
 * Support de l'upload SVG avec sanitisation DOMDocument.
 *
 * Active l'upload SVG dans la médiathèque WordPress uniquement lorsque
 * le toggle admin est activé. Chaque fichier SVG est sanitisé avant
 * d'être enregistré : scripts, attributs d'événements JS et références
 * xlink dangereuses sont supprimés.
 *
 * Activation : toggle Greenlight > SVG > enable_svg.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retourne true si l'upload SVG est activé.
 *
 * @return bool
 */
function greenlight_svg_enabled() {
	$opts = get_option( 'greenlight_svg_options', array( 'enable_svg' => 0 ) );

	return ! empty( $opts['enable_svg'] );
}

/**
 * Ajoute SVG à la liste des MIME types autorisés à l'upload.
 *
 * @param array $mimes MIME types autorisés.
 * @return array
 */
function greenlight_allow_svg_upload( $mimes ) {
	if ( greenlight_svg_enabled() ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'greenlight_allow_svg_upload' );

/**
 * Sanitise un fichier SVG avant que WordPress ne l'enregistre.
 *
 * Supprime : éléments <script>, attributs d'événements JS (on*),
 * href/xlink:href pointant vers javascript:, éléments <use> externes.
 *
 * @param array $file Données du fichier uploadé.
 * @return array
 */
function greenlight_sanitize_svg( $file ) {
	if ( ! greenlight_svg_enabled() ) {
		return $file;
	}

	if ( empty( $file['tmp_name'] ) || empty( $file['type'] ) ) {
		return $file;
	}

	if ( 'image/svg+xml' !== $file['type'] ) {
		return $file;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$raw = file_get_contents( $file['tmp_name'] );

	if ( false === $raw ) {
		$file['error'] = __( 'Impossible de lire le fichier SVG.', 'greenlight' );
		return $file;
	}

	$clean = greenlight_sanitize_svg_string( $raw );

	if ( false === $clean ) {
		$file['error'] = __( 'Le fichier SVG est invalide ou ne peut pas être sanitisé.', 'greenlight' );
		return $file;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	file_put_contents( $file['tmp_name'], $clean );

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'greenlight_sanitize_svg' );

/**
 * Sanitise une chaîne SVG via DOMDocument.
 *
 * @param string $svg Balisage SVG brut.
 * @return string|false SVG sanitisé, ou false en cas d'échec du parsing.
 */
function greenlight_sanitize_svg_string( $svg ) {
	if ( ! class_exists( 'DOMDocument' ) ) {
		return false;
	}

	$dom = new DOMDocument();
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMDocument API uses camelCase properties.
	$dom->formatOutput = false;
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMDocument API uses camelCase properties.
	$dom->preserveWhiteSpace = false;

	libxml_use_internal_errors( true );
	$loaded = $dom->loadXML( $svg, LIBXML_NONET );
	libxml_clear_errors();

	if ( ! $loaded ) {
		return false;
	}

	$xpath = new DOMXPath( $dom );

	// Supprime tous les éléments <script>.
	foreach ( $xpath->query( '//script' ) as $node ) {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode API uses camelCase properties.
		$node->parentNode->removeChild( $node );
	}

	// Supprime les attributs d'événements JS (on*) et href/xlink:href vers javascript:.
	foreach ( $xpath->query( '//*' ) as $element ) {
		if ( ! $element instanceof DOMElement ) {
			continue;
		}

		$to_remove = array();

		foreach ( $element->attributes as $attr ) {
			$name = strtolower( $attr->name );

			// Attributs événementiels (onclick, onload, onmouseover...).
			if ( 0 === strpos( $name, 'on' ) ) {
				$to_remove[] = $attr->name;
				continue;
			}

			// href / xlink:href pointant vers du JavaScript.
			if ( in_array( $name, array( 'href', 'xlink:href' ), true ) ) {
				$value = strtolower( trim( $attr->value ) );
				if ( 0 === strpos( $value, 'javascript:' ) ) {
					$to_remove[] = $attr->name;
				}
			}
		}

		foreach ( $to_remove as $attr_name ) {
			$element->removeAttribute( $attr_name );
		}
	}

	// Supprime les éléments <use> pointant vers des ressources externes.
	foreach ( $xpath->query( '//use' ) as $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}

		$href = $node->getAttribute( 'href' );

		if ( '' === $href ) {
			$href = $node->getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' );
		}

		if ( '' !== $href && ( '#' !== $href[0] ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode API uses camelCase properties.
			$node->parentNode->removeChild( $node );
		}
	}

	return $dom->saveXML();
}

/**
 * Corrige la vérification du type MIME pour les SVG dans la médiathèque.
 *
 * WordPress valide le contenu du fichier contre le MIME type déclaré ;
 * les fichiers SVG peuvent être détectés comme 'text/plain'.
 * Ce filtre rétablit le bon type.
 *
 * @param array  $data     Données du fichier vérifié.
 * @param string $file     Chemin vers le fichier.
 * @param string $filename Nom du fichier.
 * @param array  $mimes    MIME types autorisés.
 * @return array
 */
function greenlight_fix_svg_mime_check( $data, $file, $filename, $mimes ) {
	if ( ! greenlight_svg_enabled() ) {
		return $data;
	}

	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	if ( ! in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
		return $data;
	}

	return array(
		'ext'             => $ext,
		'type'            => 'image/svg+xml',
		'proper_filename' => $data['proper_filename'],
	);
}
add_filter( 'wp_check_filetype_and_ext', 'greenlight_fix_svg_mime_check', 10, 4 );
