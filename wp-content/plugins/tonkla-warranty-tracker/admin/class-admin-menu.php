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
use ITWT\Label_Presenter;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Component_Repo;
use ITWT\Repositories\Contract_Repo;
use ITWT\Repositories\Customer_Repo;
use ITWT\Repositories\Service_Log_Repo;
use ITWT\Repositories\Support_Request_Repo;
use ITWT\Service_Tag;
use ITWT\Settings;
use ITWT\Sla_Options;
use ITWT\Spec_Icons;

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
		add_action( 'admin_footer', array( $this, 'render_developer_credit' ) );
	}

	/**
	 * Developer credit footer on every ITWT admin screen (print-safe / toggleable).
	 */
	public function render_developer_credit(): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' === $page || 0 !== strpos( $page, 'itwt-' ) ) {
			return;
		}
		// Print sticker UI is print-first; credit stays on screen pages only.
		if ( 'itwt-print-label' === $page ) {
			return;
		}
		Settings::render_developer_credit( 'admin' );
	}

	/**
	 * Register admin menu pages.
	 */
	public function menus(): void {
		$new_reqs = 0;
		if ( current_user_can( 'itwt_log_service' ) ) {
			$new_reqs = ( new Support_Request_Repo() )->count_by_status( 'new' );
		}

		$menu_title = __( 'TK Warranty Lookup', 'tonkla-warranty-tracker' );
		if ( $new_reqs > 0 ) {
			$menu_title = sprintf(
				/* translators: 1: menu title 2: new request count */
				'%1$s <span class="awaiting-mod">%2$d</span>',
				$menu_title,
				$new_reqs
			);
		}

		add_menu_page(
			__( 'Tonkla Warranty Tracker', 'tonkla-warranty-tracker' ),
			$menu_title,
			'itwt_log_service',
			'itwt-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-shield', // Menu icon
			58
		);

		$req_label = __( 'คำขอ', 'tonkla-warranty-tracker' );
		if ( $new_reqs > 0 ) {
			$req_label = sprintf(
				/* translators: 1: menu label 2: count */
				'%1$s <span class="awaiting-mod">%2$d</span>',
				$req_label,
				$new_reqs
			);
		}

		add_submenu_page( 'itwt-dashboard', __( 'แดชบอร์ด', 'tonkla-warranty-tracker' ), __( 'แดชบอร์ด', 'tonkla-warranty-tracker' ), 'itwt_log_service', 'itwt-dashboard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ทรัพย์สิน', 'tonkla-warranty-tracker' ), __( 'ทรัพย์สิน', 'tonkla-warranty-tracker' ), 'itwt_manage_assets', 'itwt-assets', array( $this, 'render_assets' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ลูกค้า / คู่ค้า', 'tonkla-warranty-tracker' ), __( 'ลูกค้า / คู่ค้า', 'tonkla-warranty-tracker' ), 'itwt_manage_assets', 'itwt-customers', array( $this, 'render_customers' ) );
		add_submenu_page( 'itwt-dashboard', __( 'คำขอ', 'tonkla-warranty-tracker' ), $req_label, 'itwt_log_service', 'itwt-requests', array( $this, 'render_requests' ) );
		add_submenu_page( 'itwt-dashboard', __( 'พิมพ์สติ๊กเกอร์', 'tonkla-warranty-tracker' ), __( 'พิมพ์สติ๊กเกอร์', 'tonkla-warranty-tracker' ), 'itwt_manage_assets', 'itwt-print-label', array( $this, 'render_print' ) );
		add_submenu_page( 'itwt-dashboard', __( 'ตั้งค่า', 'tonkla-warranty-tracker' ), __( 'ตั้งค่า', 'tonkla-warranty-tracker' ), 'itwt_manage_settings', 'itwt-settings', array( $this, 'render_settings' ) );
		add_submenu_page( 'itwt-dashboard', __( 'คู่มือใช้งาน', 'tonkla-warranty-tracker' ), __( 'คู่มือใช้งาน', 'tonkla-warranty-tracker' ), 'itwt_log_service', 'itwt-help', array( $this, 'render_help' ) );
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
		wp_enqueue_script( 'itwt-admin', ITWT_PLUGIN_URL . 'admin/js/admin.js', array(), ITWT_VERSION, true );

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
				array( 'itwt-admin', 'itwt-qrcode' ),
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

		if ( 'itwt-settings' === $page ) {
			wp_enqueue_media();
		}

		$templates = get_option( 'itwt_spec_templates', array() );
		wp_localize_script(
			'itwt-admin',
			'itwtAdmin',
			array(
				'specTemplates'   => $templates,
				'specIcons'       => Spec_Icons::options(),
				'customersRest'   => esc_url_raw( rest_url( 'itwt/v1/customers' ) ),
				'assetsRest'      => esc_url_raw( rest_url( 'itwt/v1/assets' ) ),
				'restNonce'       => wp_create_nonce( 'wp_rest' ),
				'i18n'            => array(
					'confirmDelete'      => __( 'ยืนยันการลบรายการนี้? การกระทำนี้อาจไม่สามารถย้อนกลับได้', 'tonkla-warranty-tracker' ),
					'confirmDeleteTitle' => __( 'ยืนยันการลบ', 'tonkla-warranty-tracker' ),
					'confirmDeleteBulk'  => __( 'ยืนยันการลบรายการที่เลือก? การกระทำนี้อาจไม่สามารถย้อนกลับได้', 'tonkla-warranty-tracker' ),
					'confirmOk'          => __( 'ลบ', 'tonkla-warranty-tracker' ),
					'confirmCancel'      => __( 'ยกเลิก', 'tonkla-warranty-tracker' ),
					'bulkNeedAction'     => __( 'เลือกการดำเนินการแบบกลุ่มก่อน', 'tonkla-warranty-tracker' ),
					'bulkNeedItems'      => __( 'เลือกอย่างน้อย 1 รายการ', 'tonkla-warranty-tracker' ),
					'logoTitle'          => __( 'เลือกโลโก้', 'tonkla-warranty-tracker' ),
					'logoButton'         => __( 'ใช้รูปนี้', 'tonkla-warranty-tracker' ),
					'addCustomer'        => __( 'เพิ่มรายชื่อ', 'tonkla-warranty-tracker' ),
					'editCustomer'       => __( 'แก้ไขรายชื่อ', 'tonkla-warranty-tracker' ),
					'customerSearch'     => __( 'ค้นหารหัสหรือชื่อลูกค้า', 'tonkla-warranty-tracker' ),
					'customerNone'       => __( 'ไม่พบรายชื่อ', 'tonkla-warranty-tracker' ),
					'customerClear'      => __( 'ไม่ระบุลูกค้า', 'tonkla-warranty-tracker' ),
					'customerSaved'      => __( 'บันทึกรายชื่อแล้ว — เลือกให้อัตโนมัติ', 'tonkla-warranty-tracker' ),
					'assetNone'          => __( 'ไม่พบสินทรัพย์', 'tonkla-warranty-tracker' ),
					'assetRemove'        => __( 'ลบออก', 'tonkla-warranty-tracker' ),
					'assetMax'           => __( 'เลือกได้สูงสุด 50 รายการ', 'tonkla-warranty-tracker' ),
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
			case 'bulk_customers':
				$this->bulk_customers();
				break;
			case 'save_contract':
				$this->save_contract();
				break;
			case 'delete_contract':
				$this->delete_contract();
				break;
			case 'save_service_log':
				$this->save_service_log();
				break;
			case 'delete_service_log':
				$this->delete_service_log();
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
			case 'import_contacts':
				$this->import_contacts();
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
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
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
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
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
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( (string) $_GET['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( in_array( $action, array( 'edit', 'new' ), true ) ) {
			$asset     = $id ? ( new Asset_Repo() )->find( $id ) : null;
			$contracts = $asset ? ( new Contract_Repo() )->for_asset( (int) $asset->id ) : array();
			$logs      = $asset ? ( new Service_Log_Repo() )->for_asset( (int) $asset->id ) : array();
			$components = $asset ? ( new Component_Repo() )->for_asset( (int) $asset->id ) : array();
			$templates = get_option( 'itwt_spec_templates', array() );
			$ma_count  = $asset ? ( new Contract_Repo() )->count_by_type( (int) $asset->id, 'ma' ) : 0;

			$picked_customer_id = isset( $_GET['itwt_customer_id'] ) ? absint( $_GET['itwt_customer_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$asset_customer_id  = (int) ( $asset->customer_id ?? 0 );
			$selected_customer_id = $picked_customer_id > 0 ? $picked_customer_id : $asset_customer_id;
			$selected_customer    = $selected_customer_id > 0 ? ( new Customer_Repo() )->find( $selected_customer_id ) : null;

			include ITWT_PLUGIN_DIR . 'admin/views/asset-form.php';
			return;
		}

		$table = new Asset_List_Table();
		$table->prepare_items();
		$import_job = get_option( 'itwt_csv_import_job', null );
		include ITWT_PLUGIN_DIR . 'admin/views/asset-list.php';
	}

	/** Customers / partners. */
	public function render_customers(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$party_role = isset( $_GET['party_role'] ) ? sanitize_key( wp_unslash( (string) $_GET['party_role'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page       = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$per_page   = Customer_Repo::normalize_per_page( isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 20 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$result     = ( new Customer_Repo() )->list(
			array(
				'search'     => $search,
				'party_role' => $party_role,
				'page'       => $page,
				'per_page'   => $per_page,
			)
		);
		$edit       = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$customer   = $edit ? ( new Customer_Repo() )->find( $edit ) : null;
		$import_result = get_transient( 'itwt_contact_import_' . get_current_user_id() );
		include ITWT_PLUGIN_DIR . 'admin/views/customer-list.php';
	}

	/** Support requests. */
	public function render_requests(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		$table = new Request_List_Table();
		$table->prepare_items();
		include ITWT_PLUGIN_DIR . 'admin/views/request-list.php';
	}

	/** Print labels. */
	public function render_print(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		$ids = array();
		if ( ! empty( $_GET['ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw = sanitize_text_field( wp_unslash( (string) $_GET['ids'] ) );
			$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
		}
		$repo    = new Asset_Repo();
		$id_list = array_slice( $ids, 0, 50 );
		$map     = $repo->find_many( $id_list );
		$assets  = array();
		foreach ( $id_list as $id ) {
			if ( isset( $map[ $id ] ) ) {
				$assets[] = $map[ $id ];
			}
		}
		$asset_ids      = array_map( static fn( $a ) => (int) $a->id, $assets );
		$contracts_map  = ( new Contract_Repo() )->for_assets( $asset_ids );
		$settings       = Settings::get();
		$label_payloads = array();
		foreach ( $assets as $asset ) {
			$aid              = (int) $asset->id;
			$label_payloads[] = Label_Presenter::present(
				$asset,
				$contracts_map[ $aid ] ?? array(),
				$settings
			);
		}
		include ITWT_PLUGIN_DIR . 'admin/views/print-label.php';
	}

	/** Settings. */
	public function render_settings(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		$settings = Settings::get();
		include ITWT_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/** In-plugin help / onboarding. */
	public function render_help(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		include ITWT_PLUGIN_DIR . 'admin/views/help.php';
	}

	/** Save asset + optional first contract. */
	private function save_asset(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_asset' );

		$id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;

		$specs = array();
		if ( ! empty( $_POST['spec_key'] ) && is_array( $_POST['spec_key'] ) ) {
			$keys     = array_map( 'sanitize_text_field', wp_unslash( $_POST['spec_key'] ) );
			$vals     = isset( $_POST['spec_value'] ) && is_array( $_POST['spec_value'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['spec_value'] ) ) : array();
			$icons    = isset( $_POST['spec_icon'] ) && is_array( $_POST['spec_icon'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['spec_icon'] ) ) : array();
			$public   = isset( $_POST['spec_public'] ) && is_array( $_POST['spec_public'] ) ? wp_unslash( $_POST['spec_public'] ) : array();
			$on_label = isset( $_POST['spec_on_label'] ) && is_array( $_POST['spec_on_label'] ) ? wp_unslash( $_POST['spec_on_label'] ) : array();
			foreach ( $keys as $i => $key ) {
				if ( '' === $key ) {
					continue;
				}
				$specs[] = array(
					'key'      => $key,
					'value'    => $vals[ $i ] ?? '',
					'icon'     => Spec_Icons::sanitize( (string) ( $icons[ $i ] ?? '' ) ),
					'public'   => ! empty( $public[ $i ] ),
					'on_label' => ! empty( $on_label[ $i ] ),
				);
			}
		}

		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		$result = ( new Asset_Repo() )->save(
			array(
				'service_tag'             => isset( $_POST['service_tag'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['service_tag'] ) ) : '',
				'brand'                   => isset( $_POST['brand'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['brand'] ) ) : '',
				'serial_number'           => isset( $_POST['serial_number'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['serial_number'] ) ) : '',
				'asset_type'              => isset( $_POST['asset_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['asset_type'] ) ) : Asset_Types::default_slug(),
				'model_name'              => isset( $_POST['model_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['model_name'] ) ) : '',
				'specs_json'              => $specs,
				'customer_id'             => isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0,
				'install_location'        => isset( $_POST['install_location'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['install_location'] ) ) : '',
				'status'                  => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( (string) $_POST['status'] ) ) : 'active',
				'purchase_date'           => isset( $_POST['purchase_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['purchase_date'] ) ) : '',
				'warranty_end_date'       => isset( $_POST['warranty_end_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['warranty_end_date'] ) ) : '',
				'renew_max_years_enabled' => ! empty( $_POST['renew_max_years_enabled'] ),
				'renew_max_years'         => isset( $_POST['renew_max_years'] ) ? absint( $_POST['renew_max_years'] ) : 0,
				'renew_max_times_enabled' => ! empty( $_POST['renew_max_times_enabled'] ),
				'renew_max_times'         => isset( $_POST['renew_max_times'] ) ? absint( $_POST['renew_max_times'] ) : 0,
				'internal_note'           => isset( $_POST['internal_note'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['internal_note'] ) ) : '',
			),
			$id
		);

		if ( is_wp_error( $result ) ) {
			$wpdb->query( 'ROLLBACK' );
			$redirect = $id > 0
				? admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $id )
				: admin_url( 'admin.php?page=itwt-assets&action=new' );
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $redirect ) );
			exit;
		}

		$asset_id = (int) $result;

		$components = array();
		if ( ! empty( $_POST['comp_name'] ) && is_array( $_POST['comp_name'] ) ) {
			$names = array_map( 'sanitize_text_field', wp_unslash( $_POST['comp_name'] ) );
			$srcs  = isset( $_POST['comp_source'] ) && is_array( $_POST['comp_source'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['comp_source'] ) ) : array();
			$ends  = isset( $_POST['comp_warranty_end'] ) && is_array( $_POST['comp_warranty_end'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['comp_warranty_end'] ) ) : array();
			$notes = isset( $_POST['comp_note'] ) && is_array( $_POST['comp_note'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['comp_note'] ) ) : array();
			foreach ( $names as $i => $name ) {
				$components[] = array(
					'item_name'    => $name,
					'source'       => $srcs[ $i ] ?? '',
					'warranty_end' => $ends[ $i ] ?? '',
					'note'         => $notes[ $i ] ?? '',
				);
			}
		}
		$comp_result = ( new Component_Repo() )->replace_for_asset( $asset_id, $components );
		if ( is_wp_error( $comp_result ) ) {
			$wpdb->query( 'ROLLBACK' );
			wp_safe_redirect(
				add_query_arg(
					'error',
					rawurlencode( $comp_result->get_error_message() ),
					admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id )
				)
			);
			exit;
		}

		$purchase = isset( $_POST['purchase_date'] ) ? \ITWT\Dates::parse_input( sanitize_text_field( wp_unslash( (string) $_POST['purchase_date'] ) ) ) : null;
		$wend     = isset( $_POST['warranty_end_date'] ) ? \ITWT\Dates::parse_input( sanitize_text_field( wp_unslash( (string) $_POST['warranty_end_date'] ) ) ) : null;
		$sync     = ( new Contract_Repo() )->sync_company_warranty( $asset_id, $purchase, $wend );
		if ( is_wp_error( $sync ) ) {
			$wpdb->query( 'ROLLBACK' );
			wp_safe_redirect(
				add_query_arg(
					'error',
					rawurlencode( $sync->get_error_message() ),
					admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id )
				)
			);
			exit;
		}

		if ( ! empty( $_POST['new_contract_type'] ) && ! empty( $_POST['new_start_date'] ) && ! empty( $_POST['new_end_date'] ) ) {
			$c = ( new Contract_Repo() )->save(
				array(
					'asset_id'      => $asset_id,
					'contract_type' => sanitize_key( wp_unslash( (string) $_POST['new_contract_type'] ) ),
					'contract_no'   => isset( $_POST['new_contract_no'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['new_contract_no'] ) ) : '',
					'start_date'    => sanitize_text_field( wp_unslash( (string) $_POST['new_start_date'] ) ),
					'end_date'      => sanitize_text_field( wp_unslash( (string) $_POST['new_end_date'] ) ),
					'sla_tier'      => Sla_Options::from_request(
						isset( $_POST['new_sla_tier'] ) ? wp_unslash( $_POST['new_sla_tier'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sla_Options::from_request
						isset( $_POST['new_sla_tier_other'] ) ? wp_unslash( (string) $_POST['new_sla_tier_other'] ) : ''
					),
					'status'        => 'active',
				)
			);
			if ( is_wp_error( $c ) ) {
				$wpdb->query( 'ROLLBACK' );
				wp_safe_redirect(
					add_query_arg(
						'error',
						rawurlencode( $c->get_error_message() ),
						admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id )
					)
				);
				exit;
			}
		}

		$wpdb->query( 'COMMIT' );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id . '&updated=1' ) );
		exit;
	}

	private function delete_asset(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_asset' );
		$id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		( new Asset_Repo() )->delete( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-assets&deleted=1' ) );
		exit;
	}

	private function save_customer(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_customer' );
		$id     = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$result = ( new Customer_Repo() )->save(
			array(
				'customer_code'     => isset( $_POST['customer_code'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['customer_code'] ) ) : '',
				'company_name'      => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['company_name'] ) ) : '',
				'party_role'        => isset( $_POST['party_role'] ) ? sanitize_key( wp_unslash( (string) $_POST['party_role'] ) ) : 'customer',
				'customer_type'     => isset( $_POST['customer_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['customer_type'] ) ) : 'juristic',
				'tax_id'            => isset( $_POST['tax_id'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['tax_id'] ) ) : '',
				'business_location' => isset( $_POST['business_location'] ) ? sanitize_key( wp_unslash( (string) $_POST['business_location'] ) ) : 'thai',
				'address'           => isset( $_POST['address'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['address'] ) ) : '',
				'postal_code'       => isset( $_POST['postal_code'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['postal_code'] ) ) : '',
				'contact_name'      => isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['contact_name'] ) ) : '',
				'phone'             => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['phone'] ) ) : '',
				'mobile'            => isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['mobile'] ) ) : '',
				'office_phone'      => isset( $_POST['office_phone'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['office_phone'] ) ) : '',
				'email'             => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( (string) $_POST['email'] ) ) : '',
			),
			$id
		);
		$return = isset( $_POST['itwt_return'] ) ? esc_url_raw( wp_unslash( (string) $_POST['itwt_return'] ) ) : '';
		$return = wp_validate_redirect( $return, '' );

		if ( is_wp_error( $result ) ) {
			set_transient(
				'itwt_customer_notice_' . get_current_user_id(),
				array(
					'type'    => 'error',
					'message' => $result->get_error_message(),
				),
				60
			);
			if ( '' !== $return ) {
				wp_safe_redirect( add_query_arg( 'error', '1', $return ) );
				exit;
			}
			wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&error=1' ) );
			exit;
		}

		if ( '' !== $return ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'itwt_customer_id' => (int) $result,
						'customer_saved'   => 1,
					),
					remove_query_arg( array( 'itwt_customer_id', 'customer_saved', 'error' ), $return )
				)
			);
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&updated=1' ) );
		exit;
	}

	private function delete_customer(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_customer' );
		$id     = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$result = ( new Customer_Repo() )->delete( $id );
		if ( is_wp_error( $result ) ) {
			set_transient(
				'itwt_customer_notice_' . get_current_user_id(),
				array(
					'type'    => 'error',
					'message' => $result->get_error_message(),
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&error=1' ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&deleted=1' ) );
		exit;
	}

	private function bulk_customers(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_bulk_customers' );

		$ids = isset( $_POST['customer_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['customer_ids'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = array_values( array_filter( $ids ) );
		$bulk = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( (string) $_POST['bulk_action'] ) ) : '';

		if ( empty( $ids ) || '' === $bulk ) {
			set_transient(
				'itwt_customer_notice_' . get_current_user_id(),
				array(
					'type'    => 'error',
					'message' => __( 'เลือกอย่างน้อย 1 รายการ และระบุการดำเนินการ', 'tonkla-warranty-tracker' ),
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&error=1' ) );
			exit;
		}

		$repo = new Customer_Repo();
		$key  = 'itwt_customer_notice_' . get_current_user_id();

		if ( 'delete' === $bulk ) {
			$stats = $repo->delete_many( $ids );
			set_transient(
				$key,
				array(
					'type'    => 'success',
					'message' => sprintf(
						/* translators: 1: deleted count, 2: skipped count */
						__( 'ลบแล้ว %1$d รายการ (ข้าม %2$d เพราะมีทรัพย์สินผูกอยู่)', 'tonkla-warranty-tracker' ),
						(int) $stats['deleted'],
						(int) $stats['skipped']
					),
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers' ) );
			exit;
		}

		$role_map = array(
			'set_customer' => 'customer',
			'set_partner'  => 'partner',
			'set_both'     => 'both',
		);
		if ( isset( $role_map[ $bulk ] ) ) {
			$updated = $repo->update_party_role_many( $ids, $role_map[ $bulk ] );
			if ( is_wp_error( $updated ) ) {
				set_transient(
					$key,
					array(
						'type'    => 'error',
						'message' => $updated->get_error_message(),
					),
					60
				);
				wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&error=1' ) );
				exit;
			}
			set_transient(
				$key,
				array(
					'type'    => 'success',
					'message' => sprintf(
						/* translators: %d: updated count */
						__( 'อัปเดตบทบาทแล้ว %d รายการ', 'tonkla-warranty-tracker' ),
						(int) $updated
					),
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers' ) );
			exit;
		}

		set_transient(
			$key,
			array(
				'type'    => 'error',
				'message' => __( 'การดำเนินการไม่รองรับ', 'tonkla-warranty-tracker' ),
			),
			60
		);
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&error=1' ) );
		exit;
	}

	private function save_contract(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_contract' );
		$asset_id    = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$contract_id = isset( $_POST['contract_id'] ) ? absint( $_POST['contract_id'] ) : 0;
		$repo        = new Contract_Repo();

		if ( $contract_id > 0 ) {
			$existing = $repo->find( $contract_id );
			if ( ! $existing || (int) $existing->asset_id !== $asset_id ) {
				wp_safe_redirect(
					add_query_arg(
						'error',
						rawurlencode( __( 'ไม่พบสัญญา', 'tonkla-warranty-tracker' ) ),
						admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id ) . '#contracts'
					)
				);
				exit;
			}
		}

		$start = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['start_date'] ) ) : '';
		$end   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['end_date'] ) ) : '';
		$type  = isset( $_POST['contract_type'] ) ? sanitize_key( wp_unslash( (string) $_POST['contract_type'] ) ) : 'ma';
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( (string) $_POST['status'] ) ) : 'active';
		if ( 0 === $contract_id ) {
			$status = 'active';
		}

		$result = $repo->save(
			array(
				'asset_id'      => $asset_id,
				'contract_type' => $type,
				'contract_no'   => isset( $_POST['contract_no'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['contract_no'] ) ) : '',
				'start_date'    => $start,
				'end_date'      => $end,
				'sla_tier'      => Sla_Options::from_request(
					isset( $_POST['sla_tier'] ) ? wp_unslash( $_POST['sla_tier'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sla_Options::from_request
					isset( $_POST['sla_tier_other'] ) ? wp_unslash( (string) $_POST['sla_tier_other'] ) : ''
				),
				'status'        => $status,
			),
			$contract_id
		);

		$url = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id ) . '#contracts';
		if ( is_wp_error( $result ) ) {
			$url = add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $url );
			wp_safe_redirect( $url );
			exit;
		}

		$start_iso = \ITWT\Dates::parse_input( $start );
		$end_iso   = \ITWT\Dates::parse_input( $end );
		if ( 'warranty' === $type && 'active' === $status && $start_iso && $end_iso ) {
			$this->sync_asset_warranty_dates( $asset_id, $start_iso, $end_iso );
		}

		wp_safe_redirect( add_query_arg( 'updated', '1', $url ) );
		exit;
	}

	/**
	 * Keep asset purchase / warranty end aligned with active warranty contract.
	 *
	 * @param int    $asset_id Asset ID.
	 * @param string $start    ISO start.
	 * @param string $end      ISO end.
	 */
	private function sync_asset_warranty_dates( int $asset_id, string $start, string $end ): void {
		global $wpdb;
		$wpdb->update(
			\ITWT\Installer::table( 'assets' ),
			array(
				'purchase_date'     => $start,
				'warranty_end_date' => $end,
				'updated_at'        => current_time( 'mysql' ),
			),
			array( 'id' => $asset_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
		$this->bust_asset_lookup( $asset_id );
	}

	/**
	 * Bust public lookup cache for an asset id.
	 *
	 * @param int $asset_id Asset ID.
	 */
	private function bust_asset_lookup( int $asset_id ): void {
		$asset = ( new Asset_Repo() )->find( $asset_id );
		if ( $asset && ! empty( $asset->service_tag ) ) {
			( new Asset_Repo() )->bust_cache( (string) $asset->service_tag );
		}
	}

	private function delete_contract(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_contract' );
		$asset_id    = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$contract_id = isset( $_POST['contract_id'] ) ? absint( $_POST['contract_id'] ) : 0;
		$repo        = new Contract_Repo();
		$existing    = $repo->find( $contract_id );
		$url         = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id ) . '#contracts';

		if ( ! $existing || (int) $existing->asset_id !== $asset_id ) {
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( __( 'ไม่พบสัญญา', 'tonkla-warranty-tracker' ) ), $url ) );
			exit;
		}

		$was_warranty = 'warranty' === (string) $existing->contract_type && 'active' === (string) $existing->status;
		$result       = $repo->delete( $contract_id );
		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $url ) );
			exit;
		}

		if ( $was_warranty ) {
			global $wpdb;
			$table = \ITWT\Installer::table( 'assets' );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET purchase_date = NULL, warranty_end_date = NULL, updated_at = %s WHERE id = %d",
					current_time( 'mysql' ),
					$asset_id
				)
			);
			$this->bust_asset_lookup( $asset_id );
		}

		wp_safe_redirect( add_query_arg( 'updated', '1', $url ) );
		exit;
	}

	private function save_service_log(): void {
		if ( ! current_user_can( 'itwt_log_service' ) && ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_save_service_log' );
		$asset_id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$log_id   = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
		$repo     = new Service_Log_Repo();
		$url      = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id ) . '#service';

		if ( $log_id > 0 ) {
			$existing = $repo->find( $log_id );
			if ( ! $existing || (int) $existing->asset_id !== $asset_id ) {
				wp_safe_redirect( add_query_arg( 'error', rawurlencode( __( 'ไม่พบบันทึกบริการ', 'tonkla-warranty-tracker' ) ), $url ) );
				exit;
			}
		}

		$result = $repo->save(
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

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( $result->get_error_message() ), $url ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( 'updated', '1', $url ) );
		exit;
	}

	private function delete_service_log(): void {
		if ( ! current_user_can( 'itwt_log_service' ) && ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_delete_service_log' );
		$asset_id = isset( $_POST['asset_id'] ) ? absint( $_POST['asset_id'] ) : 0;
		$log_id   = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
		$repo     = new Service_Log_Repo();
		$existing = $repo->find( $log_id );
		$url      = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id ) . '#service';

		if ( ! $existing || (int) $existing->asset_id !== $asset_id ) {
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( __( 'ไม่พบบันทึกบริการ', 'tonkla-warranty-tracker' ) ), $url ) );
			exit;
		}

		if ( ! $repo->delete( $log_id ) ) {
			wp_safe_redirect( add_query_arg( 'error', rawurlencode( __( 'ลบบันทึกบริการไม่สำเร็จ', 'tonkla-warranty-tracker' ) ), $url ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( 'updated', '1', $url ) );
		exit;
	}

	private function update_request_status(): void {
		if ( ! current_user_can( 'itwt_log_service' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_update_request' );
		$id     = isset( $_POST['request_id'] ) ? absint( $_POST['request_id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( (string) $_POST['status'] ) ) : 'new';
		( new Support_Request_Repo() )->update_status( $id, $status );
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-requests&updated=1' ) );
		exit;
	}

	/**
	 * Sanitize portal theme slug.
	 *
	 * @param string $theme Raw theme.
	 */
	private function sanitize_portal_theme( string $theme ): string {
		return in_array( $theme, array( 'soft', 'minimal', 'dense' ), true ) ? $theme : 'soft';
	}

	private function save_settings(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
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
				'company_name_th'          => isset( $_POST['company_name_th'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['company_name_th'] ) ) : '',
				'company_name_en'          => isset( $_POST['company_name_en'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['company_name_en'] ) ) : '',
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
				'portal_title'             => isset( $_POST['portal_title'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['portal_title'] ) ) : '',
				'portal_description'       => isset( $_POST['portal_description'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['portal_description'] ) ) : '',
				'portal_accent_color'      => isset( $_POST['portal_accent_color'] ) ? (string) sanitize_hex_color( wp_unslash( (string) $_POST['portal_accent_color'] ) ) : '',
				'portal_theme'             => $this->sanitize_portal_theme( isset( $_POST['portal_theme'] ) ? sanitize_key( wp_unslash( (string) $_POST['portal_theme'] ) ) : 'soft' ),
				'privacy_policy_url'       => isset( $_POST['privacy_policy_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['privacy_policy_url'] ) ) : '',
				'show_masked_serial'       => ! empty( $_POST['show_masked_serial'] ),
				'show_specs'               => ! empty( $_POST['show_specs'] ),
				'show_contracts'           => ! empty( $_POST['show_contracts'] ),
				'show_service_logs'        => ! empty( $_POST['show_service_logs'] ),
				'show_line_button'         => ! empty( $_POST['show_line_button'] ),
				'show_support_button'      => ! empty( $_POST['show_support_button'] ),
				'show_purchase_date'       => ! empty( $_POST['show_purchase_date'] ),
				'show_warranty_end'        => ! empty( $_POST['show_warranty_end'] ),
				'show_sla'                 => ! empty( $_POST['show_sla'] ),
				'show_coverage_bar'        => ! empty( $_POST['show_coverage_bar'] ),
				// Locked on: developer credit is a free-license condition (toggle is disabled in the UI).
				'show_developer_credit'    => true,
			)
		);
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-settings&updated=1' ) );
		exit;
	}

	private function confirm_tech_group(): void {
		if ( ! current_user_can( 'itwt_manage_settings' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
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
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
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

	private function import_contacts(): void {
		if ( ! current_user_can( 'itwt_manage_assets' ) ) {
			wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla-warranty-tracker' ) );
		}
		check_admin_referer( 'itwt_import_contacts' );
		$file   = isset( $_FILES['contact_file'] ) ? $_FILES['contact_file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$result = ( new \ITWT\Contact_Importer() )->import_upload( is_array( $file ) ? $file : array() );
		$key    = 'itwt_contact_import_' . get_current_user_id();
		if ( is_wp_error( $result ) ) {
			set_transient(
				$key,
				array(
					'ok'      => false,
					'message' => $result->get_error_message(),
				),
				120
			);
		} else {
			set_transient(
				$key,
				array(
					'ok'      => true,
					'created' => (int) ( $result['created'] ?? 0 ),
					'updated' => (int) ( $result['updated'] ?? 0 ),
					'skipped' => (int) ( $result['skipped'] ?? 0 ),
					'errors'  => array_slice( $result['errors'] ?? array(), 0, 20 ),
				),
				120
			);
		}
		wp_safe_redirect( admin_url( 'admin.php?page=itwt-customers&imported=1' ) );
		exit;
	}

	private function ajax_generate_tag_redirect(): void {
		// Unused placeholder — tag generation is client-side / on save.
	}
}
