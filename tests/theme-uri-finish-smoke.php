<?php
/**
 * Smoke test ensuring theme metadata uses a valid Theme URI.
 *
 * @package Greenlight
 */

$style  = file_get_contents( __DIR__ . '/../style.css' );
$bundle = file_get_contents( __DIR__ . '/../assets/css/greenlight-bundle.css' );

if ( false === $style || false === $bundle ) {
	fwrite( STDERR, "Unable to read style.css or assets/css/greenlight-bundle.css\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		'Theme URI: hhttps://github.com/benabot/greenlight',
	) as $needle
) {
	if ( false !== strpos( $style, $needle ) ) {
		$failures[] = 'style.css still contains invalid Theme URI.';
	}

	if ( false !== strpos( $bundle, $needle ) ) {
		$failures[] = 'assets/css/greenlight-bundle.css still contains invalid Theme URI.';
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Theme URI finish smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Theme URI finish smoke test passed.\n" );
