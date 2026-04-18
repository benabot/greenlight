<?php
/**
 * Smoke test ensuring admin performance forms remain saveable.
 *
 * @package Greenlight
 */

$admin = file_get_contents( __DIR__ . '/../inc/admin.php' );

if ( false === $admin ) {
	fwrite( STDERR, "Unable to read inc/admin.php\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		"<form method=\"post\" action=\"options.php\">",
		"settings_fields( 'greenlight_performance' );",
		"Enregistrer la diffusion",
		"Enregistrer le cache HTML",
		"Enregistrer le rythme",
		"Enregistrer la maintenance",
	) as $needle
) {
	if ( false === strpos( $admin, $needle ) ) {
		$failures[] = 'inc/admin.php is missing "' . $needle . '".';
	}
}

if ( false !== strpos( $admin, '<form method="post" action="options.php"><form' ) ) {
	$failures[] = 'inc/admin.php still appears to nest performance save forms.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Performance admin save smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Performance admin save smoke test passed.\n" );
