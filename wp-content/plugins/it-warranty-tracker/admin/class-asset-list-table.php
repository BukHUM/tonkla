<?php
/**
 * Assets WP_List_Table.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Admin;

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;
use ITWT\Asset_Types;
use ITWT\Coverage;
use ITWT\Labels;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Contract_Repo;
use ITWT\Settings;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Asset list table for admin.
 */
final class Asset_List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'asset',
				'plural'   => 'assets',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Columns.
	 *
	 * @return array<string,string>
	 */
	public function get_columns(): array {
		return array(
			'cb'          => '<input type="checkbox" />',
			'qr'          => __( 'QR', 'it-warranty-tracker' ),
			'service_tag' => __( 'Service Tag', 'it-warranty-tracker' ),
			'brand'       => __( 'ยี่ห้อ', 'it-warranty-tracker' ),
			'model_name'  => __( 'รุ่น', 'it-warranty-tracker' ),
			'asset_type'  => __( 'ประเภท', 'it-warranty-tracker' ),
			'coverage'    => __( 'ความคุ้มครอง', 'it-warranty-tracker' ),
			'status'      => __( 'สถานะ', 'it-warranty-tracker' ),
			'actions'     => __( 'จัดการ', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array<string,array{0:string,1:bool}>
	 */
	protected function get_sortable_columns(): array {
		return array(
			'service_tag' => array( 'service_tag', false ),
			'brand'       => array( 'brand', false ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array<string,string>
	 */
	protected function get_bulk_actions(): array {
		return array(
			'print_labels' => __( 'พิมพ์สติ๊กเกอร์ที่เลือก', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Handle bulk print redirect.
	 */
	public function process_bulk_action(): void {
		if ( 'print_labels' !== $this->current_action() ) {
			return;
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
			return;
		}

		$ids = isset( $_REQUEST['asset_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['asset_ids'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = array_values( array_filter( $ids ) );
		if ( empty( $ids ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=itwt-print-label&ids=' . implode( ',', array_slice( $ids, 0, 50 ) ) ) );
		exit;
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items(): void {
		$this->process_bulk_action();

		$per_page = Admin_List::request_per_page();
		$page     = $this->get_pagenum();
		$search   = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type     = isset( $_REQUEST['asset_type'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['asset_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$coverage = isset( $_REQUEST['coverage'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['coverage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$result = ( new Asset_Repo() )->list(
			array(
				'search'     => $search,
				'asset_type' => $type,
				'coverage'   => $coverage,
				'page'       => $page,
				'per_page'   => $per_page,
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = $result['items'];
		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Suppress default WP tablenav — toolbar/footer live in the view.
	 *
	 * @param string $which Top or bottom.
	 */
	protected function display_tablenav( $which ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Intentionally empty.
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Row.
	 */
	protected function column_cb( $item ): string {
		$id = (int) $item->id;
		return sprintf(
			'<label class="screen-reader-text" for="cb-asset-%1$d">%2$s</label><input type="checkbox" class="itwt-cb-row" id="cb-asset-%1$d" name="asset_ids[]" value="%1$d" />',
			$id,
			esc_html(
				sprintf(
					/* translators: %s: service tag */
					__( 'เลือก %s', 'it-warranty-tracker' ),
					(string) $item->service_tag
				)
			)
		);
	}

	/**
	 * Inline QR for lookup URL.
	 *
	 * @param object $item Row.
	 */
	protected function column_qr( $item ): string {
		$url   = Settings::lookup_url( (string) $item->service_tag );
		$print = admin_url( 'admin.php?page=itwt-print-label&ids=' . (int) $item->id );

		return sprintf(
			'<a class="itwt-list-qr" href="%1$s" title="%2$s" data-qr-url="%3$s" aria-label="%2$s"><span class="itwt-list-qr__canvas" aria-hidden="true"></span></a>',
			esc_url( $print ),
			esc_attr(
				sprintf(
					/* translators: %s: service tag */
					__( 'พิมพ์สติ๊กเกอร์ QR ของ %s', 'it-warranty-tracker' ),
					(string) $item->service_tag
				)
			),
			esc_attr( $url )
		);
	}

	/**
	 * Service tag with actions.
	 *
	 * @param object $item Row.
	 */
	protected function column_service_tag( $item ): string {
		$edit = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $item->id );
		return '<code><a class="itwt-tag-link" href="' . esc_url( $edit ) . '">' . esc_html( (string) $item->service_tag ) . '</a></code>';
	}

	/**
	 * Manage column — same link style as customer list.
	 *
	 * @param object $item Row.
	 */
	protected function column_actions( $item ): string {
		$edit  = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $item->id );
		$print = admin_url( 'admin.php?page=itwt-print-label&ids=' . (int) $item->id );
		return '<div class="itwt-row-actions">'
			. '<a class="itwt-row-action" href="' . esc_url( $edit ) . '">' . esc_html__( 'แก้ไข', 'it-warranty-tracker' ) . '</a>'
			. '<a class="itwt-row-action" href="' . esc_url( $print ) . '">' . esc_html__( 'พิมพ์สติ๊กเกอร์', 'it-warranty-tracker' ) . '</a>'
			. '</div>';
	}

	/**
	 * Default column.
	 *
	 * @param object $item        Row.
	 * @param string $column_name Column.
	 */
	protected function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'brand':
			case 'model_name':
				return esc_html( (string) $item->{$column_name} );
			case 'status':
				return esc_html( Labels::asset_status( (string) $item->status ) );
			case 'asset_type':
				return esc_html( Asset_Types::label( (string) $item->asset_type ) );
			case 'coverage':
				$contracts = ( new Contract_Repo() )->for_asset( (int) $item->id );
				$cov       = Coverage::compute( (string) $item->status, $contracts );
				return esc_html( Coverage::label( $cov['status'], $cov['days_remaining'] ) );
			case 'actions':
				return $this->column_actions( $item );
			default:
				return '';
		}
	}

	/**
	 * Filters live in the page toolbar (itwt-panel__toolbar), not tablenav.
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Intentionally empty — shared toolbar in asset-list.php.
	}
}
