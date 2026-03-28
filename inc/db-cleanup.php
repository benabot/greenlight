<?php
/**
 * Database cleanup utilities.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */

/**
 * Deletes old post revisions.
 *
 * @return int Number of revisions deleted.
 */
function greenlight_cleanup_revisions() {
	global $wpdb;

	$revisions = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'"
	);

	$count = 0;

	foreach ( $revisions as $revision_id ) {
		if ( wp_delete_post_revision( $revision_id ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Deletes auto-draft posts.
 *
 * @return int Number of auto-drafts deleted.
 */
function greenlight_cleanup_autodraft() {
	global $wpdb;

	$autodrafts = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
	);

	$count = 0;

	foreach ( $autodrafts as $post_id ) {
		if ( wp_delete_post( $post_id, true ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Permanently deletes trashed posts.
 *
 * @return int Number of trashed posts deleted.
 */
function greenlight_cleanup_trash() {
	global $wpdb;

	$trashed = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'trash'"
	);

	$count = 0;

	foreach ( $trashed as $post_id ) {
		if ( wp_delete_post( $post_id, true ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Deletes spam comments.
 *
 * @return int Number of spam comments deleted.
 */
function greenlight_cleanup_spam_comments() {
	global $wpdb;

	$spam = $wpdb->get_col(
		"SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
	);

	$count = 0;

	foreach ( $spam as $comment_id ) {
		if ( wp_delete_comment( $comment_id, true ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Deletes expired transients.
 *
 * @return int Number of expired transients deleted.
 */
function greenlight_cleanup_expired_transients() {
	global $wpdb;

	$time = time();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$count = $wpdb->query(
		$wpdb->prepare(
			"DELETE a, b FROM {$wpdb->options} a
			INNER JOIN {$wpdb->options} b ON b.option_name = REPLACE(a.option_name, '_timeout_', '_')
			WHERE a.option_name LIKE %s
			AND a.option_value < %d",
			$wpdb->esc_like( '_transient_timeout_' ) . '%',
			$time
		)
	);

	return max( 0, (int) $count / 2 );
}

/**
 * Optimizes database tables.
 *
 * @return int Number of tables optimized.
 */
function greenlight_optimize_tables() {
	global $wpdb;

	$tables = $wpdb->get_col( 'SHOW TABLES' );
	$count  = 0;

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "OPTIMIZE TABLE `{$table}`" );
		++$count;
	}

	return $count;
}

/**
 * Handles individual cleanup actions via admin_post.
 *
 * @return void
 */
function greenlight_handle_db_cleanup() {
	check_admin_referer( 'greenlight_db_cleanup' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$task = isset( $_POST['cleanup_task'] ) ? sanitize_key( $_POST['cleanup_task'] ) : '';

	$tasks = array(
		'revisions'  => 'greenlight_cleanup_revisions',
		'autodraft'  => 'greenlight_cleanup_autodraft',
		'trash'      => 'greenlight_cleanup_trash',
		'spam'       => 'greenlight_cleanup_spam_comments',
		'transients' => 'greenlight_cleanup_expired_transients',
		'optimize'   => 'greenlight_optimize_tables',
	);

	$count = 0;

	if ( isset( $tasks[ $task ] ) ) {
		$count = call_user_func( $tasks[ $task ] );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=performance&cleanup=' . $task . '&cleaned=' . $count ) );
	exit;
}
add_action( 'admin_post_greenlight_db_cleanup', 'greenlight_handle_db_cleanup' );

/**
 * Schedules the weekly database cleanup cron.
 *
 * @return void
 */
function greenlight_schedule_db_cleanup() {
	$perf = get_option( 'greenlight_performance_options', array() );

	if ( empty( $perf['enable_auto_cleanup'] ) ) {
		wp_clear_scheduled_hook( 'greenlight_db_cleanup_cron' );
		return;
	}

	if ( ! wp_next_scheduled( 'greenlight_db_cleanup_cron' ) ) {
		wp_schedule_event( time(), 'weekly', 'greenlight_db_cleanup_cron' );
	}
}
add_action( 'init', 'greenlight_schedule_db_cleanup' );

/**
 * Runs all cleanup tasks via cron.
 *
 * @return void
 */
function greenlight_run_scheduled_cleanup() {
	greenlight_cleanup_revisions();
	greenlight_cleanup_autodraft();
	greenlight_cleanup_trash();
	greenlight_cleanup_spam_comments();
	greenlight_cleanup_expired_transients();
}
add_action( 'greenlight_db_cleanup_cron', 'greenlight_run_scheduled_cleanup' );

/**
 * Cleans up cron on theme switch.
 *
 * @return void
 */
function greenlight_clear_db_cleanup_cron() {
	wp_clear_scheduled_hook( 'greenlight_db_cleanup_cron' );
}
add_action( 'switch_theme', 'greenlight_clear_db_cleanup_cron' );

/* phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */
