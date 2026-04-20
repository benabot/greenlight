<?php
/**
 * Smoke test ensuring appearance option updates purge the HTML cache.
 *
 * @package Greenlight
 */

$cache = file_get_contents( __DIR__ . '/../inc/cache.php' );

if ( false === $cache ) {
	fwrite( STDERR, "Unable to read inc/cache.php\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		"update_option_' . GREENLIGHT_APPEARANCE_OPTION_KEY",
		"add_option_' . GREENLIGHT_APPEARANCE_OPTION_KEY",
		"delete_option_' . GREENLIGHT_APPEARANCE_OPTION_KEY",
		'greenlight_purge_cache_on_appearance_change',
	) as $needle
) {
	if ( false === strpos( $cache, $needle ) ) {
		$failures[] = 'Missing appearance cache purge hook: ' . $needle;
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Appearance cache purge smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Appearance cache purge smoke test passed.\n" );
