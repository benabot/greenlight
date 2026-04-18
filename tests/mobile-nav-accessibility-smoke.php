<?php
/**
 * Smoke test for accessible mobile navigation markup.
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

if ( false !== strpos( $contents, 'type="checkbox"' ) || false !== strpos( $contents, "type=\"checkbox\"" ) ) {
	$failures[] = 'Header still uses a checkbox as the burger control.';
}

if ( false !== strpos( $contents, '<label for="nav-toggle"' ) ) {
	$failures[] = 'Header still uses a label tied to nav-toggle.';
}

if ( false === strpos( $contents, '<details class="site-nav-disclosure">' ) ) {
	$failures[] = 'Header does not contain a native details disclosure for the mobile menu.';
}

if ( false === strpos( $contents, '<summary class="nav-burger">' ) ) {
	$failures[] = 'Header does not contain a summary control for the mobile menu.';
}

if ( false === strpos( $contents, '<nav class="site-nav"' ) ) {
	$failures[] = 'Header does not contain the navigation landmark inside the mobile disclosure.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Mobile navigation accessibility smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Mobile navigation accessibility smoke test passed.\n" );
