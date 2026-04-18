<?php
/**
 * Smoke test ensuring docs no longer claim PHPCS is globally clean.
 *
 * @package Greenlight
 */

$project_state = file_get_contents( __DIR__ . '/../PROJECT_STATE.md' );
$todo          = file_get_contents( __DIR__ . '/../TODO.md' );

if ( false === $project_state || false === $todo ) {
	fwrite( STDERR, "Unable to read PROJECT_STATE.md or TODO.md\n" );
	exit( 1 );
}

$failures = array();

foreach (
	array(
		'PHPCS global du thème sans erreurs ni warnings',
		'PHPCS zéro erreur sur 30 fichiers PHP modifiés',
		'scan complet zéro erreur/warning',
		'PHPCS et `php -l` sur les fichiers PHP modifiés — zéro erreur',
	) as $claim
) {
	if ( false !== strpos( $project_state, $claim ) || false !== strpos( $todo, $claim ) ) {
		$failures[] = 'Docs still contain PHPCS claim: ' . $claim;
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "PHPCS doc alignment smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "PHPCS doc alignment smoke test passed.\n" );
