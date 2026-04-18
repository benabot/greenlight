<?php
/**
 * Smoke test ensuring import/export covers redirects.
 *
 * @package Greenlight
 */

$admin_file = __DIR__ . '/../inc/admin.php';
$contents   = file_get_contents( $admin_file );

if ( false === $contents ) {
	fwrite( STDERR, "Unable to read inc/admin.php\n" );
	exit( 1 );
}

$failures = array();

if ( false === strpos( $contents, "'redirects'                  => get_option( 'greenlight_redirects', array() )" ) ) {
	$failures[] = 'Export payload does not include greenlight_redirects.';
}

if ( false === strpos( $contents, "'redirects'   => array( 'greenlight_redirects'" ) ) {
	$failures[] = 'Import map does not include greenlight_redirects.';
}

if ( false === strpos( $contents, 'Exportez et importez les réglages et redirections sans toucher au contenu.' ) ) {
	$failures[] = 'Tools tab wording still overstates or misstates the import/export perimeter.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Import/export redirects smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Import/export redirects smoke test passed.\n" );
