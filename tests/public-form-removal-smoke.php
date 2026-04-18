<?php
/**
 * Smoke test for removing public forms and broken newsletter anchor.
 *
 * @package Greenlight
 */

$checks = array(
	__DIR__ . '/../home.php'                 => array(
		'newsletter-form',
		'greenlight_newsletter',
	),
	__DIR__ . '/../single.php'               => array(
		'newsletter-form',
		'greenlight_newsletter',
	),
	__DIR__ . '/../patterns/contact.php'     => array(
		'<form',
		'name="action" value="greenlight_contact"',
	),
	__DIR__ . '/../header.php'               => array(
		'href="#newsletter"',
	),
	__DIR__ . '/../front-page.php'           => array(
		'greenlight-preview-newsletter',
		'<form class="newsletter-form"',
	),
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
	fwrite( STDERR, "Public form removal smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Public form removal smoke test passed.\n" );
