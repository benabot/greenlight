<?php
/**
 * Smoke test ensuring the Performance admin tab does not nest forms.
 *
 * @package Greenlight
 */

$admin_file = __DIR__ . '/../inc/admin.php';
$contents   = file_get_contents( $admin_file );

if ( false === $contents ) {
	fwrite( STDERR, "Unable to read inc/admin.php\n" );
	exit( 1 );
}

$start = strpos( $contents, 'function greenlight_render_admin_tab_performance() {' );
$end   = strpos( $contents, 'function greenlight_render_admin_tab_svg() {' );

if ( false === $start || false === $end || $end <= $start ) {
	fwrite( STDERR, "Unable to isolate greenlight_render_admin_tab_performance()\n" );
	exit( 1 );
}

$function_source = substr( $contents, $start, $end - $start );

if ( false === preg_match_all( '/<\/?form\b[^>]*>/i', $function_source, $matches ) ) {
	fwrite( STDERR, "Unable to scan form tags in performance tab source\n" );
	exit( 1 );
}

$depth        = 0;
$max_depth    = 0;
$form_counter = 0;

foreach ( $matches[0] as $tag ) {
	if ( 0 === stripos( $tag, '</form' ) ) {
		$depth = max( 0, $depth - 1 );
		continue;
	}

	++$form_counter;
	++$depth;
	$max_depth = max( $max_depth, $depth );
}

if ( $max_depth > 1 ) {
	fwrite(
		STDERR,
		"Performance admin tab nests forms (max depth: {$max_depth}, forms found: {$form_counter}).\n"
	);
	exit( 1 );
}

fwrite( STDOUT, "Performance admin tab form nesting smoke test passed.\n" );
