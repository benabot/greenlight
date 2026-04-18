<?php
/**
 * Smoke test ensuring front assets are no longer generated lazily at runtime.
 *
 * @package Greenlight
 */

$functions = file_get_contents( __DIR__ . '/../functions.php' );
$concat    = file_get_contents( __DIR__ . '/../inc/concat.php' );
$minify    = file_get_contents( __DIR__ . '/../inc/minify.php' );
$readme    = file_get_contents( __DIR__ . '/../README.md' );
$build     = file_get_contents( __DIR__ . '/../bin/minify.sh' );

if ( false === $functions || false === $concat || false === $minify || false === $readme || false === $build ) {
	fwrite( STDERR, "Unable to read asset runtime/build files\n" );
	exit( 1 );
}

$failures = array();

if ( false !== strpos( $functions, 'greenlight_ensure_min_file' ) ) {
	$failures[] = 'functions.php still triggers lazy minified asset generation.';
}

if ( false !== strpos( $concat, 'greenlight_generate_bundle();' ) ) {
	$failures[] = 'inc/concat.php still generates the CSS bundle during front enqueue.';
}

if ( false !== strpos( $minify, 'function greenlight_ensure_min_file' ) ) {
	$failures[] = 'inc/minify.php still exposes lazy file generation.';
}

if ( false === strpos( $build, 'greenlight-bundle.css' ) ) {
	$failures[] = 'bin/minify.sh does not generate the deployment CSS bundle.';
}

foreach (
	array(
		'genere automatiquement',
		'a la volee',
		'ou a la volee',
	) as $claim
) {
	if ( false !== strpos( $readme, $claim ) ) {
		$failures[] = 'README.md still advertises runtime asset generation: ' . $claim;
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Assets deployment build smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Assets deployment build smoke test passed.\n" );
