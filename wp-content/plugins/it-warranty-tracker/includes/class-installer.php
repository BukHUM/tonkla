<?php
/**
 * Database schema, capabilities, and role setup.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Handles activation, deactivation, and schema migrations.
 */
final class Installer {

	/**
	 * Activation callback.
	 */
	public static function activate(): void {
		self::create_tables();
		self::seed_options();
		self::register_caps_and_role();
		self::schedule_crons();
		update_option( 'itwt_db_version', ITWT_DB_VERSION, false );
		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback.
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'itwt_daily_expiry_notify' );
		wp_clear_scheduled_hook( 'itwt_daily_retention_cleanup' );
		wp_clear_scheduled_hook( 'itwt_csv_import_batch' );
		flush_rewrite_rules();
	}

	/**
	 * Run migrations when DB version mismatches.
	 */
	public static function maybe_upgrade(): void {
		$current = get_option( 'itwt_db_version', '' );
		if ( ITWT_DB_VERSION === $current ) {
			return;
		}
		self::create_tables();
		self::seed_options();
		self::register_caps_and_role();
		self::schedule_crons();
		update_option( 'itwt_db_version', ITWT_DB_VERSION, false );
	}

	/**
	 * Schedule recurring cron events.
	 */
	public static function schedule_crons(): void {
		if ( ! wp_next_scheduled( 'itwt_daily_expiry_notify' ) ) {
			$tomorrow = strtotime( 'tomorrow 08:00:00' );
			if ( false === $tomorrow ) {
				$tomorrow = time() + DAY_IN_SECONDS;
			}
			wp_schedule_event( $tomorrow, 'daily', 'itwt_daily_expiry_notify' );
		}
		if ( ! wp_next_scheduled( 'itwt_daily_retention_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'itwt_daily_retention_cleanup' );
		}
	}

	/**
	 * Create / update custom tables via dbDelta.
	 */
	public static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$prefix  = $wpdb->prefix . 'itwt_';

		$sql = array();

		$sql[] = "CREATE TABLE {$prefix}customers (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_code VARCHAR(50) NOT NULL,
			company_name VARCHAR(255) NOT NULL,
			contact_name VARCHAR(150) NOT NULL DEFAULT '',
			phone VARCHAR(50) NOT NULL DEFAULT '',
			email VARCHAR(150) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY customer_code (customer_code),
			KEY company_name (company_name)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}assets (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			service_tag VARCHAR(20) NOT NULL,
			brand VARCHAR(50) NOT NULL,
			serial_number VARCHAR(100) DEFAULT NULL,
			asset_type VARCHAR(30) NOT NULL,
			model_name VARCHAR(255) NOT NULL DEFAULT '',
			specs_json LONGTEXT NULL,
			customer_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			install_location VARCHAR(255) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			internal_note TEXT NULL,
			created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			updated_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY service_tag (service_tag),
			UNIQUE KEY brand_serial (brand, serial_number),
			KEY serial_number (serial_number),
			KEY status_asset_type (status, asset_type),
			KEY customer_id (customer_id)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}contracts (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			asset_id BIGINT UNSIGNED NOT NULL,
			contract_type VARCHAR(20) NOT NULL,
			contract_no VARCHAR(50) NOT NULL DEFAULT '',
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			sla_tier VARCHAR(100) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY asset_type_start (asset_id, contract_type, start_date),
			KEY asset_end (asset_id, end_date),
			KEY status_end (status, end_date)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}service_logs (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			asset_id BIGINT UNSIGNED NOT NULL,
			service_date DATE NOT NULL,
			service_type VARCHAR(30) NOT NULL,
			symptom TEXT NULL,
			resolution TEXT NULL,
			public_summary VARCHAR(500) DEFAULT NULL,
			technician_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY asset_service_date (asset_id, service_date)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}support_requests (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			asset_id BIGINT UNSIGNED NOT NULL,
			request_type VARCHAR(20) NOT NULL,
			contact_name VARCHAR(150) NOT NULL,
			phone VARCHAR(50) NOT NULL,
			detail TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			dedupe_hash CHAR(64) NOT NULL,
			consent_at DATETIME NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY dedupe_hash (dedupe_hash),
			KEY asset_id (asset_id),
			KEY status_created (status, created_at)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}line_subscriptions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			asset_id BIGINT UNSIGNED NOT NULL,
			line_user_id VARCHAR(64) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			consent_at DATETIME NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY asset_line_user (asset_id, line_user_id),
			KEY line_user_status (line_user_id, status)
		) {$charset};";

		$sql[] = "CREATE TABLE {$prefix}notification_log (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			subscription_id BIGINT UNSIGNED NOT NULL,
			contract_id BIGINT UNSIGNED NOT NULL,
			milestone SMALLINT NOT NULL,
			result VARCHAR(20) NOT NULL,
			sent_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY sub_contract_milestone (subscription_id, contract_id, milestone),
			KEY sent_at (sent_at)
		) {$charset};";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}
	}

	/**
	 * Seed default options (autoload = false).
	 */
	public static function seed_options(): void {
		$defaults = array(
			'logo_attachment_id'     => 0,
			'support_phone'          => '',
			'lookup_page_id'         => 0,
			'expiring_days'          => 30,
			'milestones'             => array( 60, 30, 7, 0 ),
			'request_retention_months' => 24,
			'line_tech_group_id'     => '',
			'line_pending_group_id'  => '',
			'line_oa_id'             => '',
			'line_monthly_quota'     => 300,
			'line_customer_reserve'  => 150,
			'pdpa_consent_text'      => __( 'ข้าพเจ้ายินยอมให้เก็บข้อมูลติดต่อเพื่อติดตามคำขอซ่อม/ต่อ MA ตามระยะเวลาที่กำหนด', 'it-warranty-tracker' ),
			'line_pdpa_text'         => __( 'คุณจะได้รับการแจ้งเตือนสถานะประกันของเครื่องที่ผูกไว้ทาง LINE ตามระยะเวลาที่กำหนด และสามารถยกเลิกได้ทุกเมื่อ', 'it-warranty-tracker' ),
			'asset_types'            => array(
				array(
					'slug'  => 'hardware',
					'label' => 'Hardware',
				),
				array(
					'slug'  => 'system',
					'label' => 'System',
				),
				array(
					'slug'  => 'software',
					'label' => 'Software',
				),
			),
		);

		$existing = get_option( 'itwt_settings', false );
		if ( false === $existing ) {
			add_option( 'itwt_settings', $defaults, '', false );
		} else {
			update_option( 'itwt_settings', array_merge( $defaults, (array) $existing ), false );
		}

		$spec_templates = array(
			'hardware' => array(
				array( 'key' => 'CPU', 'value' => '', 'public' => true ),
				array( 'key' => 'RAM', 'value' => '', 'public' => true ),
				array( 'key' => 'Storage', 'value' => '', 'public' => true ),
			),
			'system'   => array(
				array( 'key' => 'Platform', 'value' => '', 'public' => true ),
				array( 'key' => 'Modules', 'value' => '', 'public' => true ),
			),
			'software' => array(
				array( 'key' => 'Version', 'value' => '', 'public' => true ),
				array( 'key' => 'License seats', 'value' => '', 'public' => true ),
			),
		);

		if ( false === get_option( 'itwt_spec_templates', false ) ) {
			add_option( 'itwt_spec_templates', $spec_templates, '', false );
		}
	}

	/**
	 * Register custom capabilities and technician role.
	 */
	public static function register_caps_and_role(): void {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'itwt_manage_assets' );
			$admin->add_cap( 'itwt_log_service' );
			$admin->add_cap( 'itwt_manage_settings' );
		}

		$tech = get_role( 'itwt_technician' );
		if ( ! $tech ) {
			add_role(
				'itwt_technician',
				__( 'ITWT Technician', 'it-warranty-tracker' ),
				array(
					'read'             => true,
					'itwt_log_service' => true,
				)
			);
		} else {
			$tech->add_cap( 'read' );
			$tech->add_cap( 'itwt_log_service' );
		}
	}

	/**
	 * Table name helper.
	 *
	 * @param string $suffix Table suffix without prefix (e.g. assets).
	 */
	public static function table( string $suffix ): string {
		global $wpdb;
		return $wpdb->prefix . 'itwt_' . $suffix;
	}
}
