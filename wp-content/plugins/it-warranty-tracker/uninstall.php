<?php
/**
 * Uninstall IT Warranty Tracker.
 *
 * @package ITWT
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$tables = array(
	'itwt_notification_log',
	'itwt_line_subscriptions',
	'itwt_support_requests',
	'itwt_service_logs',
	'itwt_contracts',
	'itwt_asset_components',
	'itwt_assets',
	'itwt_customers',
);

foreach ( $tables as $table ) {
	$name = $wpdb->prefix . $table;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$name}" );
}

delete_option( 'itwt_db_version' );
delete_option( 'itwt_settings' );
delete_option( 'itwt_spec_templates' );
delete_option( 'itwt_csv_import_job' );

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_itwt_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_itwt_' ) . '%'
	)
);

$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'itwt_manage_assets' );
	$admin->remove_cap( 'itwt_log_service' );
	$admin->remove_cap( 'itwt_manage_settings' );
}

remove_role( 'itwt_technician' );

wp_clear_scheduled_hook( 'itwt_daily_expiry_notify' );
wp_clear_scheduled_hook( 'itwt_daily_retention_cleanup' );
wp_clear_scheduled_hook( 'itwt_csv_import_batch' );
wp_clear_scheduled_hook( 'itwt_notify_tech_group' );
