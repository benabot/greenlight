<?php
/**
 * Smoke test for the visual appearance reset entry point.
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

if ( false === strpos( $contents, "'appearance'  => __( 'Apparence', 'greenlight' )" ) ) {
	$failures[] = 'Admin shell does not expose the Appearance tab.';
}

if ( false === strpos( $contents, "function greenlight_handle_reset_appearance()" ) ) {
	$failures[] = 'Appearance reset handler is missing.';
}

if ( false === strpos( $contents, "add_action( 'admin_post_greenlight_reset_appearance', 'greenlight_handle_reset_appearance' );" ) ) {
	$failures[] = 'Appearance reset admin_post hook is missing.';
}

if ( false === strpos( $contents, "name=\"greenlight_confirm_reset_appearance\"" ) ) {
	$failures[] = 'Appearance reset confirmation checkbox is missing.';
}

if ( false === strpos( $contents, "Réinitialiser l’apparence" ) ) {
	$failures[] = 'Appearance reset button label is missing.';
}

if ( false === strpos( $contents, '$defaults = greenlight_get_appearance_defaults();' ) ) {
	$failures[] = 'Appearance reset does not capture the raw defaults before updating the option.';
}

if ( false !== strpos( $contents, 'greenlight_sanitize_appearance_settings( greenlight_get_appearance_defaults() )' ) ) {
	$failures[] = 'Appearance reset still re-sanitizes defaults and can flip checkbox values back to 1.';
}

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "Appearance reset smoke test failed:\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

fwrite( STDOUT, "Appearance reset smoke test passed.\n" );
