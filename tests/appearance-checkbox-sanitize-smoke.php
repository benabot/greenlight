<?php
/**
 * Smoke test ensuring appearance checkbox sanitization respects explicit 0 values.
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

if ( false === strpos( $contents, '$sanitize_flag = static function ( $key ) use ( $input ) {' ) ) {
	$failures[] = 'Appearance sanitize flag helper is missing.';
}

foreach (
	array(
		'header_sticky',
		'show_tagline',
		'hero_cta_enabled',
		'hero_cta2_enabled',
		'show_hero_badge',
	) as $flag
) {
	if ( false === strpos( $contents, "\$sanitize_flag( '" . $flag . "' )" ) ) {
		$failures[] = 'Appearance sanitize helper is not used for ' . $flag . '.';
	}

	if ( false !== strpos( $contents, "isset( \$input['" . $flag . "'] ) ? 1 : 0" ) ) {
		$failures[] = 'Appearance sanitize still uses isset() for ' . $flag . '.';
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Appearance checkbox sanitize smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Appearance checkbox sanitize smoke test passed.\n" );
