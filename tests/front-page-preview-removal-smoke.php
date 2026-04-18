<?php
/**
 * Smoke test ensuring the dead front-page preview mechanism is removed.
 *
 * @package Greenlight
 */

$front_page = file_get_contents( __DIR__ . '/../front-page.php' );
$functions  = file_get_contents( __DIR__ . '/../functions.php' );

if ( false === $front_page || false === $functions ) {
	fwrite( STDERR, "Unable to read front-page.php or functions.php\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		'greenlight_is_admin_preview_request',
		'greenlight-preview-',
		'data-greenlight-page-title',
		'data-greenlight-page-excerpt',
	) as $needle
) {
	if ( false !== strpos( $front_page, $needle ) ) {
		$failures[] = 'front-page.php still contains "' . $needle . '".';
	}
}

if ( false !== strpos( $functions, 'greenlight-admin-preview' ) ) {
	$failures[] = 'functions.php still enqueues the dedicated admin preview stylesheet.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Front-page preview removal smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Front-page preview removal smoke test passed.\n" );
