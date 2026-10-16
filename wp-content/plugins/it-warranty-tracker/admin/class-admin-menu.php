<?php
/**
 * WP Admin menus and form handlers.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Admin;

defined( 'ABSPATH' ) || exit;

use ITWT\Asset_Types;
use ITWT\Csv_Importer;
use ITWT\Installer;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Contract_Repo;
use ITWT\Repositories\Customer_Repo;
use ITWT\Repositories\Service_Log_Repo;
use ITWT\Repositories\Support_Request_Repo;
use ITWT\Service_Tag;
use ITWT\Settings;

/**
 * Registers admin pages and handles POST actions.
 */
final class Admin_Menu {

	/**
	 * Register menus and hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_post_itwt_export_csv', array( $this, 'handle_export' ) );
	}

	/**
	 * Register admin menu pages.
	 */
	public function menus(): void {
		add_menu_page(
			__( 'IT Warranty', 'it-warranty-tracker' ),
			__( 'IT Warranty', 'it-warranty-tracker' ),
			'itwt_log_service',
			'itwt-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-shield',
			58
		);

		add_submenu_page( 'itwt-dashboard', __( 'แดชบอร์ด', 'it-warranty-tracker' ), __( 'แดชบอร์ด', 'it-warranty-tracker' ), 'itwt_log_service', 'itwt-dashboard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ทรัพย์สิน', 'it-warranty-tracker' ), __( 'ทรัพย์สิน', 'it-warranty-tracker' ), 'itwt_manage_assets', 'itwt-assets', array( $this, 'render_assets' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ลูกค้า', 'it-warranty-tracker' ), __( 'ลูกค้า', 'it-warranty-tracker' ), 'itwt_manage_assets', 'itwt-customers', array( $this, 'render_customers' ) );
		add_submenu_page( 'itwt-dashboard', __( 'คำขอ', 'it-warranty-tracker' ), __( 'คำขอ', 'it-warranty-tracker' ), 'itwt_log_service', 'itwt-requests', array( $this, 'render_requests' ) );
		add_submenu_page( 'itwt-dashboard', __( 'พิมพ์สติ๊กเกอร์', 'it-warranty-tracker' ), __( 'พิมพ์สติ๊กเกอร์', 'it-warranty-tracker' ), 'itwt_manage_assets', 'itwt-print-label', array( $this, 'render_print' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ตั้งค่า', 'it-warranty-tracker' ), __( 'ตั้งค่า', 'it-warranty-tracker' ), 'itwt_manage_settings', 'itwt-settings', array( $this, 'render_settings' ) );
	}

	/**
	 * Enqueue admin assets on plugin pages.
	 *
	 * @param string $hook Current admin hook.
	 */
	public function assets( string $hook ): void {
		if ( false === strpos( $hook, 'itwt-' ) && false === strpos( $hook, 'itwt_' ) ) {
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 0 !== strpos( $page, 'itwt-' ) ) {
				return;
			}
		}

		wp_enqueue_style( 'itwt-admin', ITWT_PLUGIN_URL . 'admin/css/admin.css', array(), ITWT_VERSION );
		wp_enqueue_script( 'itwt-admin', ITWT_PLUGIN_URL . 'admin/js/admin.js', array(), ITWT_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

		$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( (string) $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$need_qr = ( 'itwt-print-label' === $page )
			|| ( 'itwt-assets' === $page && ! in_array( $action, array( 'edit', 'new' ), true ) );

		if ( $need_qr ) {
			wp_enqueue_script(
				'itwt-qrcode',
				ITWT_PLUGIN_URL . 'admin/js/vendor/qrcode-generator.js',
				array(),
				'1.4.4',
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}
		if ( 'itwt-print-label' === $page ) {
			wp_enqueue_script(
				'itwt-print-label',
				ITWT_PLUGIN_URL . 'admin/js/print-label.js',
				array( 'itwt-qrcode' ),
				ITWT_VERSION,
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}
		if ( 'itwt-assets' === $page && ! in_array( $action, array( 'edit', 'new' ), true ) ) {
			wp_enqueue_script(
				'itwt-asset-list-qr',
				ITWT_PLUGIN_URL . 'admin/js/asset-list-qr.js',
				array( 'itwt-qrcode' ),
				ITWT_VERSION,
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		$templates = get_option( 'itwt_spec_templates', array() );
		wp_localize_script(
			'itwt-admin',
			'itwtAdmin',
			array(
				'specTemplates' => $templates,
				'i18n'          => array(
					'confirmDelete' => __( 'ยืนยันการลบ?', 'it-warranty-tracker' ),
				),
			)
		);
	}

	/**
	 * Handle admin form POSTs.
	 */
	public function handle_actions(): void {
		if ( empty( $_POST['itwt_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$action = sanitize_key( wp_unslash( (string) $_POST['itwt_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		switch ( $action ) {
			case 'save_asset':
				$this->save_asset();
				break;
			case 'delete_asset':
				$this->delete_asset();
				break;
			case 'save_customer':
				$this->save_customer();
				break;
			case 'delete_customer':
				$this->delete_customer();
				break;
			case 'save_contract':
				$this->save_contract();
				break;
			case 'save_service_log':
				$this->save_service_log();
				break;
			case 'update_request_status':
				$this->update_request_status();
				break;
			case 'save_settings':
				$this->save_settings();
				break;
			case 'confirm_tech_group':
				$this->confirm_tech_group();
				break;
			case 'import_csv':
				$this->import_csv();
				break;
			case 'generate_tag':
				$this->ajax_generate_tag_redirect();
				break;
		}
	}

	/**
	 * CSV export handler.
	 */
	public function handle_export(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_export_csv' );
		$result = ( new Asset_Repo() )->list( array( 'per_page' => 50, 'page' => 1 ) );
		// Export up to a few pages worth — bounded.
		$all = $result['items'];
		$total_pages = (int) ceil( $result['total'] / 50 );
		for ( $p = 2; $p <= min( 20, $total_pages ); $p++ ) {
			$chunk = ( new Asset_Repo() )->list( array( 'per_page' => 50, 'page' => $p ) );
			$all   = array_merge( $all, $chunk['items'] );
		}
		( new Csv_Importer() )->export( $all );
	}

	/** Dashboard view. */
	public function render_dashboard(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		$asset_repo = new Asset_Repo();
		$counts     = $asset_repo->coverage_counts();
		$expiring30 = $asset_repo->expiring_soon( 30, 20 );
		$expiring60 = $asset_repo->expiring_soon( 60, 20 );
		$new_reqs   = ( new Support_Request_Repo() )->count_by_status( 'new' );
		$quota      = ( new \ITWT\Line\Line_Quota() )->snapshot();
		include ITWT_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/** Assets list / form. */
	public function render_assets(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) && ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( (string) $_GET['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( in_array( $action, array( 'edit', 'new' ), true ) ) {
			$asset     = $id ? ( new Asset_Repo() )->find( $id ) : null;
			$contracts = $asset ? ( new Contract_Repo() )->for_asset( (int) $asset->id ) : array();
			$logs      = $asset ? ( new Service_Log_Repo() )->for_asset( (int) $asset->id ) : array();
			$customers = ( new Customer_Repo() )->list( array( 'per_page' => 50 ) );
			$templates = get_option( 'itwt_spec_templates', array() );
			include ITWT_PLUGIN_DIR . 'admin/views/asset-form.php';
			return;
		}

		$table = new Asset_List_Table();
		$table->prepare_items();
		$import_job = get_option( 'itwt_csv_import_job', null );
		include ITWT_PLUGIN_DIR . 'admin/views/asset-list.php';
	}

	/** Customers. */
	public function render_customers(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$result = ( new Customer_Repo() )->list( array( 'search' => $search, 'page' => $page, 'per_page' => 20 ) );
		$edit   = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$customer = $edit ? ( new Customer_Repo() )->find( $edit ) : null;
		include ITWT_PLUGIN_DIR . 'admin/views/customer-list.php';
	}

	/** Support requests. */
	public function render_requests(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		$table = new Request_List_Table();
		$table->prepare_items();
		include ITWT_PLUGIN_DIR . 'admin/views/request-list.php';
	}

	/** Print labels. */
	public function render_print(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		$ids = array();
		if ( ! empty( $_GET['ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw = sanitize_text_field( wp_unslash( (string) $_GET['ids'] ) );
			$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
		}
		$assets   = array();
		$repo     = new Asset_Repo();
		foreach ( array_slice( $ids, 0, 50 ) as $id ) {
			$a = $repo->find( $id );
			if ( $a ) {
				$assets[] = $a;
			}
		}
		$settings = Settings::get();
		include ITWT_PLUGIN_DIR . 'admin/views/print-label.php';
	}

	/** Settings. */
	public function render_settings(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		$settings = Settings::get();
		include ITWT_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/** Save asset + optional first contract. */
	private function save_asset(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_asset' );

		$id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;

		$specs = array();
		if ( ! empty( $_POST['spec_key'] ) && is_array( $_POST['spec_key'] ) ) {
			$keys   = array_map( 'sanitize_text_field', wp_unslash( $_POST['spec_key'] ) );
			$vals   = isset( $_POST['spec_value'] ) && is_array( $_POST['spec_value'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['spec_value'] ) ) : array();
			$public = isset( $_POST['spec_public'] ) && is_array( $_POST['spec_public'] ) ? wp_unslash( $_POST['spec_public'] ) : array();
			foreach ( $keys as $i => $key ) {
				if ( '' === $key ) {
					continue;
				}
				$specs[] = array(
					'key'    => $key,
					'value'  => $vals[ $i ] ?? '',
					'public' => ! empty( $public[ $i ] ),
				);
			}
		}

		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		$result = ( new Asset_Repo() )->save(
			array(
				'service_tag'      => isset( $_POST['service_tag'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['service_tag'] ) ) : '',
				'brand'            => isset( $_POST['brand'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['brand'] ) ) : '',
				'serial_number'    => isset( $_POST['serial_number'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['serial_number'] ) ) : '',
				'asset_type'       => isset( $_POST['asset_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['asset_type'] ) ) : Asset_Types::default_slug(),
				'model_name'       => isset( $_POST['model_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model_name'] ) ) : '',
				'specs_json'       => $specs,
				'customer_id'      => isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0,
				'install_location' => isset( $_POST['install_location'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['install_location'] ) ) : '',
				'status'           => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( (string) $_POST['status'] ) ) : 'active',
				'internal_note'    => isset( $_POST['internal_note'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['internal_note'] ) ) : '',
			),
			$id
		);

		if ( is_wp_error( $result ) ) {
			$wpdb->query( 'ROLLBACK' );
			add_settings_error( 'itwt', 'save', $result->get_error_message(), 'error' );
			return;
		}

		if ( ! empty( $_POST['new_contract_type'] ) && ! empty( $_POST['new_start_date'] ) && ! empty( $_POST['new_end_date'] ) ) {
			$c = ( new Contract_Repo() )->save(
				array(
					'asset_id'      => (int) $result,
					'contract_type' => sanitize_key( wp_unslash( (string) $_POST['new_contract_type'] ) ),
					'contract_no'   => isset( $_POST['new_contract_no'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['new_contract_no'] ) ) : '',
					'start_date'    => sanitize_text_field( wp_unslash( (string) $_POST['new_start_date'] ) ),
					'end_date'      => sanitize_text_field( wp_unslash( (string) $_POST['new_end_date'] ) ),
					'sla_tier'      => isset( $_POST['new_sla_tier'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['new_sla_tier'] ) ) : '',
					'status'        => 'active',
				)
			);
			if ( is_wp_error( $c ) ) {
				$wpdb->query( 'ROLLBACK' );
				add_settings_error( 'itwt', 'contract', $c->get_error_message(), 'error' );
				return;
			}
		}

		$wpdb->query( 'COMMIT' );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $result . '&updated=1' ) );
		exit;
	}

	private function delete_asset(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_asset' );
		$id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		( new Asset_Repo() )->delete( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-assets&deleted=1' ) );
		exit;
	}

	private function save_customer(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_customer' );
		$id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		( new Customer_Repo() )->save(
			array(
				'customer_code' => isset( $_POST['customer_code'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['customer_code'] ) ) : '',
				'company_name'  => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['company_name'] ) ) : '',
				'contact_name'  => isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['contact_name'] ) ) : '',
				'phone'         => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['phone'] ) ) : '',
				'email'         => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( (string) $_POST['email'] ) ) : '',
			),
			$id
		);
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&updated=1' ) );
		exit;
	}

	private function delete_customer(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_customer' );
		$id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$result = ( new Customer_Repo() )->delete( $id );
		$arg = is_wp_error( $result ) ? 'error=1' : 'deleted=1';
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&' . $arg ) );
		exit;
	}

	private function save_contract(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_contract' );
		$asset_id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$result   = ( new Contract_Repo() )->save(
			array(
				'asset_id'      => $asset_id,
				'contract_type' => isset( $_POST['contract_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['contract_type'] ) ) : 'ma',
				'contract_no'   => isset( $_POST['contract_no'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['contract_no'] ) ) : '',
				'start_date'    => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['start_date'] ) ) : '',
				'end_date'      => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['end_date'] ) ) : '',
				'sla_tier'      => isset( $_POST['sla_tier'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['sla_tier'] ) ) : '',
				'status'        => 'active',
			)
		);
		$url = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id );
		if ( is_wp_error( $result ) ) {
			$url = add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $url );
		} else {
			$url = add_query_arg( 'updated', '1', $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	private function save_service_log(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_service_log' );
		$asset_id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$log_id   = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
		( new Service_Log_Repo() )->save(
			array(
				'asset_id'       => $asset_id,
				'service_date'   => isset( $_POST['service_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['service_date'] ) ) : wp_date( 'Y-m-d' ),
				'service_type'   => isset( $_POST['service_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['service_type'] ) ) : 'claim',
				'symptom'        => isset( $_POST['symptom'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['symptom'] ) ) : '',
				'resolution'     => isset( $_POST['resolution'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['resolution'] ) ) : '',
				'public_summary' => isset( $_POST['public_summary'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['public_summary'] ) ) : '',
			),
			$log_id
		);
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id . '&updated=1' ) );
		exit;
	}

	private function update_request_status(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_update_request' );
		$id     = isset( $_POST['request_id'] ) ? absint( $_POST['request_id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( (string) $_POST['status'] ) ) : 'new';
		( new Support_Request_Repo() )->update_status( $id, $status );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-requests&updated=1' ) );
		exit;
	}

	private function save_settings(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_settings' );

		$milestones_raw = isset( $_POST['milestones'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['milestones'] ) ) : '60,30,7,0';
		$milestones     = array_values(
			array_filter(
				array_map( 'absint', explode( ',', $milestones_raw ) ),
				static fn( $n ) => $n >= 0
			)
		);

		$asset_type_rows = array();
		if ( ! empty( $_POST['asset_type_slug'] ) && is_array( $_POST['asset_type_slug'] ) ) {
			$slugs  = array_map( 'sanitize_key', wp_unslash( $_POST['asset_type_slug'] ) );
			$labels = isset( $_POST['asset_type_label'] ) && is_array( $_POST['asset_type_label'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['asset_type_label'] ) )
				: array();
			foreach ( $slugs as $i => $slug ) {
				$asset_type_rows[] = array(
					'slug'  => $slug,
					'label' => $labels[ $i ] ?? '',
				);
			}
		}

		Settings::update(
			array(
				'logo_attachment_id'       => isset( $_POST['logo_attachment_id'] ) ? absint( $_POST['logo_attachment_id'] ) : 0,
				'support_phone'            => isset( $_POST['support_phone'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['support_phone'] ) ) : '',
				'lookup_page_id'           => isset( $_POST['lookup_page_id'] ) ? absint( $_POST['lookup_page_id'] ) : 0,
				'expiring_days'            => isset( $_POST['expiring_days'] ) ? absint( $_POST['expiring_days'] ) : 30,
				'milestones'               => $milestones ?: array( 60, 30, 7, 0 ),
				'request_retention_months' => isset( $_POST['request_retention_months'] ) ? absint( $_POST['request_retention_months'] ) : 24,
				'line_oa_id'               => isset( $_POST['line_oa_id'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['line_oa_id'] ) ) : '',
				'line_monthly_quota'       => isset( $_POST['line_monthly_quota'] ) ? absint( $_POST['line_monthly_quota'] ) : 300,
				'line_customer_reserve'    => isset( $_POST['line_customer_reserve'] ) ? absint( $_POST['line_customer_reserve'] ) : 150,
				'pdpa_consent_text'        => isset( $_POST['pdpa_consent_text'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['pdpa_consent_text'] ) ) : '',
				'line_pdpa_text'           => isset( $_POST['line_pdpa_text'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['line_pdpa_text'] ) ) : '',
				'asset_types'              => Asset_Types::sanitize_list( $asset_type_rows ),
			)
		);
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-settings&updated=1' ) );
		exit;
	}

	private function confirm_tech_group(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_confirm_tech_group' );
		$settings = Settings::get();
		$pending  = (string) ( $settings['line_pending_group_id'] ?? '' );
		if ( '' !== $pending ) {
			Settings::update(
				array(
					'line_tech_group_id'    => $pending,
					'line_pending_group_id' => '',
				)
			);
		}
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-settings&updated=1' ) );
		exit;
	}

	private function import_csv(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'it-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_import_csv' );
		$file = isset( $_FILES['csv_file'] ) ? $_FILES['csv_file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$result = ( new Csv_Importer() )->queue_upload( is_array( $file ) ? $file : array() );
		$url = admin_url( 'admin.php?page=itwt-assets' );
		if ( is_wp_error( $result ) ) {
			$url = add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $url );
		} else {
			$url = add_query_arg( 'import', '1', $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	private function ajax_generate_tag_redirect(): void {
		// Unused placeholder — tag generation is client-side / on save.
	}
}
