<?php
/**
 * Main plugin bootstrap.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

use ITWT\Admin\Admin_Menu;
use ITWT\Line\Expiry_Notifier;
use ITWT\Line\Line_Quota;
use ITWT\Line\Tech_Notifier;
use ITWT\Repositories\Support_Request_Repo;
use ITWT\Repositories\Subscription_Repo;

/**
 * Registers hooks for the whole plugin.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Get singleton.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire hooks.
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( new Rest_Api(), 'register_routes' ) );
		( new Shortcode() )->register();
		( new Page_Template() )->register();

		add_action( 'itwt_daily_expiry_notify', array( new Expiry_Notifier(), 'run' ) );
		add_action( 'itwt_daily_retention_cleanup', array( $this, 'retention_cleanup' ) );
		add_action( 'itwt_notify_tech_group', array( new Tech_Notifier(), 'notify' ) );
		add_action( 'itwt_csv_import_batch', array( new Csv_Importer(), 'process_batch' ) );

		add_filter( 'plugin_action_links_' . ITWT_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		if ( is_admin() ) {
			( new Admin_Menu() )->register();
			add_action( 'admin_notices', array( $this, 'quota_admin_notice' ) );
		}

		Installer::schedule_crons();
	}

	/**
	 * Plugins list action links (Settings).
	 *
	 * @param array<int,string> $links Existing links.
	 * @return array<int,string>
	 */
	public function plugin_action_links( array $links ): array {
		if ( current_user_can( 'itwt_manage_settings' ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'admin.php?page=itwt-settings' ) ),
					esc_html__( 'ตั้งค่า', 'it-warranty-tracker' )
				)
			);
		}
		return $links;
	}

	/**
	 * Plugins list row meta (developer site).
	 *
	 * @param array<int,string> $links       Existing meta.
	 * @param string            $plugin_file Plugin basename.
	 * @return array<int,string>
	 */
	public function plugin_row_meta( array $links, string $plugin_file ): array {
		if ( ITWT_PLUGIN_BASENAME !== $plugin_file ) {
			return $links;
		}
		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( Settings::developer_credit_url( 'plugins' ) ),
			esc_html__( 'เว็บไซต์ผู้พัฒนา', 'it-warranty-tracker' )
		);
		if ( current_user_can( 'itwt_log_service' ) ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=itwt-help' ) ),
				esc_html__( 'คู่มือ', 'it-warranty-tracker' )
			);
		}
		return $links;
	}

	/**
	 * Daily retention cleanup.
	 */
	public function retention_cleanup(): void {
		( new Support_Request_Repo() )->purge_expired();
		( new Subscription_Repo() )->purge_old_logs();
	}

	/**
	 * Admin notice when LINE quota is high.
	 */
	public function quota_admin_notice(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) && ! current_user_can( 'itwt_manage_assets' ) ) {
			return;
		}

		$quota = new Line_Quota();
		$snap  = $quota->snapshot();

		if ( $snap['pct'] >= 80 ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: used, 2: quota */
						__( 'IT Warranty Tracker: โควตา LINE เดือนนี้ใช้ไป %1$d/%2$d ข้อความ', 'it-warranty-tracker' ),
						$snap['used'],
						$snap['quota']
					)
				)
			);
		}

		if ( get_transient( 'itwt_admin_notice_quota' ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'IT Warranty Tracker: หยุดแจ้งกลุ่มช่างทาง LINE ชั่วคราวเพื่อสงวนโควตาให้ลูกค้า — คำขอยังอยู่ในเมนูคำขอ', 'it-warranty-tracker' ) . '</p></div>';
		}
	}
}
