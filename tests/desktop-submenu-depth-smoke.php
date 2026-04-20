<?php
/**
 * Smoke test for desktop depth-2 submenu positioning.
 *
 * @package Greenlight
 */

$style_file = __DIR__ . '/../style.css';
$contents   = file_get_contents( $style_file );

if ( false === $contents ) {
	fwrite( STDERR, "Unable to read style.css\n" );
	exit( 1 );
}

$failures = array();

if ( false === strpos( $contents, '.site-nav .sub-menu .menu-item-has-children > .sub-menu {' ) ) {
	$failures[] = 'Nested desktop submenu rule is missing.';
}

if ( false === strpos( $contents, 'inset-inline-start: calc(100% + 0.4rem);' ) ) {
	$failures[] = 'Nested desktop submenu is not offset beside its parent item.';
}

if ( false === strpos( $contents, '.site-nav > ul > li:last-child > .sub-menu,' ) ) {
	$failures[] = 'Last desktop menu items are not explicitly realigned to stay inside the viewport.';
}

if ( false === strpos( $contents, 'inset-inline-end: calc(100% + 0.4rem);' ) ) {
	$failures[] = 'Nested desktop submenu is not mirrored back inside the viewport for right-edge items.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Desktop submenu depth smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Desktop submenu depth smoke test passed.\n" );
