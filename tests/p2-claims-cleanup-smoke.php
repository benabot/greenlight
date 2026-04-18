<?php
/**
 * Smoke test ensuring remaining inaccurate claims are cleaned up.
 *
 * @package Greenlight
 */

$readme        = file_get_contents( __DIR__ . '/../README.md' );
$project_state = file_get_contents( __DIR__ . '/../PROJECT_STATE.md' );
$todo          = file_get_contents( __DIR__ . '/../TODO.md' );

if ( false === $readme || false === $project_state || false === $todo ) {
	fwrite( STDERR, "Unable to read README.md, PROJECT_STATE.md or TODO.md\n" );
	exit( 1 );
}

$combined = $readme . "\n" . $project_state . "\n" . $todo;
$failures = array();

foreach (
	array(
		'Le theme n est pas pret pour une mise en production sans remediations sur les formulaires publics, l accessibilite mobile et l admin Performance.',
		'Responsive sans `@media` — flexbox + clamp() uniquement',
		'0 `@media` dans style.css, assets/css/blocks/*.css, assets/css/critical.css',
		'zéro `<div>` wrapper non sémantique',
		'zéro div wrapper',
		'Menu burger CSS-only',
	) as $claim
) {
	if ( false !== strpos( $combined, $claim ) ) {
		$failures[] = 'Remaining inaccurate claim: ' . $claim;
	}
}

if ( false === strpos( $readme, 'preprod solide, pas prod-ready' ) && false === strpos( $project_state, 'préprod avancée' ) ) {
	$failures[] = 'Status wording is not clearly requalified as preprod.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "P2 claims cleanup smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "P2 claims cleanup smoke test passed.\n" );
