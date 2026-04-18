<?php
/**
 * Smoke test ensuring low-contrast caption opacity is removed.
 *
 * @package Greenlight
 */

$style     = file_get_contents( __DIR__ . '/../style.css' );
$image_css = file_get_contents( __DIR__ . '/../assets/css/blocks/image.css' );

if ( false === $style || false === $image_css ) {
	fwrite( STDERR, "Unable to read style.css or assets/css/blocks/image.css\n" );
	exit( 1 );
}

$failures = array();

if ( false !== strpos( $style, ".entry-hero-media figcaption {\n\tcolor: var(--wp--preset--color--on-surface-variant);\n\topacity: 0.6;" ) ) {
	$failures[] = 'style.css still applies opacity 0.6 to entry hero figcaptions.';
}

if ( false !== strpos( $image_css, "\topacity: 0.6;" ) ) {
	$failures[] = 'assets/css/blocks/image.css still applies opacity 0.6 to block image captions.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Contrast caption smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Contrast caption smoke test passed.\n" );
