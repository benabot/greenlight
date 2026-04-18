<?php
/**
 * Smoke test for desktop navigation visibility structure.
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

if ( false === strpos( $contents, "<?php \$greenlight_render_primary_nav( 'site-nav--desktop' ); ?>" ) ) {
	$failures[] = 'Header does not expose a dedicated desktop navigation outside the mobile disclosure.';
}

if ( false === strpos( $contents, '<details class="site-nav-disclosure site-nav-disclosure--mobile">' ) ) {
	$failures[] = 'Header does not expose a dedicated mobile disclosure wrapper.';
}

if ( false === strpos( $contents, "<?php \$greenlight_render_primary_nav( 'site-nav--mobile' ); ?>" ) ) {
	$failures[] = 'Header does not expose a dedicated mobile navigation inside the disclosure.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Desktop navigation visibility smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Desktop navigation visibility smoke test passed.\n" );
