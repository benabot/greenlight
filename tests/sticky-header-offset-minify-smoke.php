<?php
/**
 * Smoke test ensuring the minified CSS keeps the sticky-header main offset selector intact.
 *
 * @package Greenlight
 */

$style_min = file_get_contents( __DIR__ . '/../style.min.css' );
$bundle    = file_get_contents( __DIR__ . '/../assets/css/greenlight-bundle.css' );

if ( false === $style_min || false === $bundle ) {
	fwrite( STDERR, "Unable to read minified sticky-header assets\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		'style.min.css'                 => $style_min,
		'assets/css/greenlight-bundle.css' => $bundle,
	) as $label => $contents
) {
	if ( false === strpos( $contents, 'body:has(.site-header--sticky) .site-main' ) ) {
		$failures[] = $label . ' does not preserve the sticky-header descendant selector.';
	}

	if ( false !== strpos( $contents, 'body:has(.site-header--sticky).site-main' ) ) {
		$failures[] = $label . ' still contains the broken sticky-header selector without descendant space.';
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Sticky header offset minify smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Sticky header offset minify smoke test passed.\n" );
