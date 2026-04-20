<?php
/**
 * Smoke test for the mobile header masthead structure.
 *
 * @package Greenlight
 */

$header_file = __DIR__ . '/../header.php';
$contents    = file_get_contents( $header_file );

if ( false === $contents ) {
	fwrite( STDERR, "Unable to read header.php\n" );
	exit( 1 );
}

$failures = array();

if ( false === strpos( $contents, '<div class="site-header__masthead">' ) ) {
	$failures[] = 'Header does not expose a dedicated masthead wrapper.';
}

if ( false === strpos( $contents, '<div class="site-branding">' ) ) {
	$failures[] = 'Header branding block is missing from the masthead.';
}

if ( false === strpos( $contents, '<details class="site-nav-disclosure site-nav-disclosure--mobile">' ) ) {
	$failures[] = 'Header does not expose the mobile disclosure inside the masthead.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Mobile header masthead smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Mobile header masthead smoke test passed.\n" );
