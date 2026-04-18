<?php
/**
 * Smoke test ensuring dead newsletter runtime remnants are removed.
 *
 * @package Greenlight
 */

$checks = array(
	__DIR__ . '/../style.css'                    => array(
		'.newsletter-cta',
		'.newsletter-form',
		'.cta-subscribe',
	),
	__DIR__ . '/../inc/admin.php'                => array(
		'newsletter_enabled',
		'newsletter_placement',
	),
	__DIR__ . '/../inc/customizer.php'           => array(
		'newsletter_enabled',
		'newsletter_placement',
	),
	__DIR__ . '/../assets/js/customizer-preview.js' => array(
		'cta-subscribe',
		'newsletter_enabled',
		'newsletter_placement',
	),
);

$failures = array();

foreach ( $checks as $path => $needles ) {
	$contents = file_get_contents( $path );

	if ( false === $contents ) {
		$failures[] = 'Unable to read ' . $path;
		continue;
	}

	foreach ( $needles as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$failures[] = basename( $path ) . ' still contains "' . $needle . '".';
		}
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Newsletter runtime removal smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Newsletter runtime removal smoke test passed.\n" );
