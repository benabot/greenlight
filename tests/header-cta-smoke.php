<?php
/**
 * Smoke test ensuring the header exposes no dead CTA/newsletter hook.
 *
 * @package Greenlight
 */

$header = file_get_contents( __DIR__ . '/../header.php' );

if ( false === $header ) {
	fwrite( STDERR, "Unable to read header.php\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		'href="#newsletter"',
		'cta-subscribe',
		'greenlight_newsletter',
	) as $needle
) {
	if ( false !== strpos( $header, $needle ) ) {
		$failures[] = 'header.php still contains "' . $needle . '".';
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Header CTA smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Header CTA smoke test passed.\n" );
