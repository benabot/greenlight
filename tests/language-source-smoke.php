<?php
/**
 * Smoke test for obvious English source strings still exposed in the theme UI.
 *
 * @package Greenlight
 */

$checks = array(
	__DIR__ . '/../home.php'                       => array( 'Read Manifesto →' ),
	__DIR__ . '/../archive.php'                    => array( 'Read Manifesto →' ),
	__DIR__ . '/../single.php'                     => array( 'PUBLISHED' ),
	__DIR__ . '/../footer.php'                     => array( 'DESIGNED FOR PERMANENCE.', '☘ LOW EMISSION MODE' ),
	__DIR__ . '/../patterns/footer.php'            => array( 'DESIGNED FOR PERMANENCE.', '☘ LOW EMISSION MODE' ),
	__DIR__ . '/../front-page.php'                 => array( 'DESIGNED FOR PERMANENCE.', '☘ LOW EMISSION MODE' ),
	__DIR__ . '/../functions.php'                  => array( 'Primary Navigation', 'Footer Navigation' ),
	__DIR__ . '/../inc/admin.php'                  => array( "'Present'" ),
	__DIR__ . '/../assets/js/customizer-preview.js' => array( 'DESIGNED FOR PERMANENCE.' ),
);

$failures = array();

foreach ( $checks as $path => $needles ) {
	$contents = file_get_contents( $path );

	if ( false === $contents ) {
		$failures[] = 'Unable to read ' . $path;
		continue;
	}

	foreach ( $needles as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$failures[] = basename( $path ) . ' still contains "' . $needle . '".';
		}
	}
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Language source smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Language source smoke test passed.\n" );
