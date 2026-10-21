<?php
/**
 * Tonkla Database Cleaner - tables, snapshots.
 * Written for the Tonkla fork; not part of upstream code.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Tonkla_DB_Installer {
	const SCHEMA_VERSION = '1.0.0';

	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'tonkla_db_' . $name;
	}

	public static function maybe_install() {
		if ( get_option( 'tonkla_db_schema_version' ) === self::SCHEMA_VERSION ) { return; }
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$scan = self::table( 'scan_results' );
		$log  = self::table( 'cleanup_log' );
		$snap = self::table( 'snapshots' );
		dbDelta( "CREATE TABLE $scan (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			item_type varchar(30) NOT NULL,
			item_name varchar(191) NOT NULL,
			item_id bigint(20) unsigned NOT NULL DEFAULT 0,
			belongs_to varchar(100) NOT NULL DEFAULT '',
			reason varchar(255) NOT NULL DEFAULT '',
			size_bytes bigint(20) unsigned NOT NULL DEFAULT 0,
			scanned_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY item_unique (item_type, item_name, item_id),
			KEY type_belongs (item_type, belongs_to)
		) $charset;" );
		dbDelta( "CREATE TABLE $log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			action_type varchar(30) NOT NULL,
			item_type varchar(30) NOT NULL DEFAULT '',
			item_name varchar(191) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at)
		) $charset;" );
		dbDelta( "CREATE TABLE $snap (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			snapshot_date date NOT NULL,
			total_bytes bigint(20) unsigned NOT NULL DEFAULT 0,
			tables_count int(11) NOT NULL DEFAULT 0,
			options_count int(11) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY snapshot_date (snapshot_date)
		) $charset;" );
		update_option( 'tonkla_db_schema_version', self::SCHEMA_VERSION, false );
	}

	public static function schedule_snapshot() {
		if ( ! wp_next_scheduled( 'tonkla_db_daily_snapshot' ) ) {
			wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'tonkla_db_daily_snapshot' );
		}
	}

	public static function take_snapshot() {
		global $wpdb;
		self::maybe_install();
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT table_name, data_length + index_length AS total FROM information_schema.tables WHERE table_schema = %s', DB_NAME ) );
		$total = 0; $count = 0;
		foreach ( (array) $rows as $r ) { $total += (int) $r->total; $count++; }
		$options_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );
		$table = self::table( 'snapshots' );
		$today = current_time( 'Y-m-d' );
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO $table (snapshot_date,total_bytes,tables_count,options_count,created_at) VALUES (%s,%d,%d,%d,%s)
			 ON DUPLICATE KEY UPDATE total_bytes=VALUES(total_bytes),tables_count=VALUES(tables_count),options_count=VALUES(options_count)",
			$today, $total, $count, $options_count, current_time( 'mysql' )
		) );
	}
}
