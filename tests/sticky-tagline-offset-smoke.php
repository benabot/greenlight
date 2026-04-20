<?php
/**
 * Smoke test for sticky header offset handling with a visible tagline.
 *
 * @package Greenlight
 */

$header_file = __DIR__ . '/../header.php';
$style_file  = __DIR__ . '/../style.css';

$header = file_get_contents( $header_file );
$style  = file_get_contents( $style_file );

if ( false === $header || false === $style ) {
	fwrite( STDERR, "Unable to read header.php or style.css\n" );
	exit( 1 );
}

$failures = array();

if ( false === strpos( $header, 'site-header--with-tagline' ) ) {
	$failures[] = 'Header does not expose a dedicated class when the tagline is rendered.';
}

if ( false === strpos( $style, 'body:has(.site-header--sticky.site-header--with-tagline)' ) ) {
	$failures[] = 'Sticky header offset is not adjusted when the tagline is visible.';
}

if ( false === strpos( $style, 'body:has(.site-header--sticky.site-header--nav-style-burger.site-header--with-tagline)' ) ) {
	$failures[] = 'Burger + sticky + tagline case does not raise the header height offset.';
}

if ( false === strpos( $style, 'body:has(.site-header--sticky.site-header--with-tagline) .page-hero' ) ) {
	$failures[] = 'Sticky hero content is not offset when the tagline makes the header taller.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Sticky tagline offset smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Sticky tagline offset smoke test passed.\n" );
