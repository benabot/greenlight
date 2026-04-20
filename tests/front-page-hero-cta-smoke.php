<?php
/**
 * Smoke test ensuring front-page hero CTAs reject bare hash URLs.
 *
 * @package Greenlight
 */

$template = file_get_contents( __DIR__ . '/../front-page.php' );

if ( false === $template ) {
	fwrite( STDERR, "Unable to read front-page.php\n" );
	exit( 1 );
}

$failures = array();

if ( false === strpos( $template, '$_gl_has_real_hero_url = static function' ) ) {
	$failures[] = 'front-page.php does not expose a dedicated hero CTA URL guard.';
}

if ( false === strpos( $template, "'' !== \$url && '#' !== \$url" ) ) {
	$failures[] = 'front-page.php does not explicitly reject a bare hash URL for hero CTAs.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Front-page hero CTA smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Front-page hero CTA smoke test passed.\n" );
