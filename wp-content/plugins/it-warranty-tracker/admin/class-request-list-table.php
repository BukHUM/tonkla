<?php
/**
 * Support requests WP_List_Table.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Admin;

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;
use ITWT\Dates;
use ITWT\Labels;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Support_Request_Repo;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Request list table.
 */
final class Request_List_Table extends \WP_List_Table {

	/**
	 * Asset rows keyed by id for current page.
	 *
	 * @var array<int,object>
	 */
	private array $assets_by_id = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'request',
				'plural'   => 'requests',
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
			'reference'    => __( 'รหัสอ้างอิง', 'it-warranty-tracker' ),
			'service_tag'  => __( 'Service Tag', 'it-warranty-tracker' ),
			'request_type' => __( 'ประเภท', 'it-warranty-tracker' ),
			'contact_name' => __( 'ผู้ติดต่อ', 'it-warranty-tracker' ),
			'phone'        => __( 'เบอร์', 'it-warranty-tracker' ),
			'status'       => __( 'สถานะ', 'it-warranty-tracker' ),
			'created_at'   => __( 'วันที่', 'it-warranty-tracker' ),
			'actions'      => __( 'จัดการ', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items(): void {
		$per_page = Admin_List::request_per_page();
		$page     = $this->get_pagenum();
		$status   = isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search   = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$result = ( new Support_Request_Repo() )->list(
			array(
				'status'   => $status,
				'search'   => $search,
				'page'     => $page,
				'per_page' => $per_page,
			)
		);

		$asset_ids = array();
		foreach ( $result['items'] as $item ) {
			$asset_ids[] = (int) $item->asset_id;
		}
		$this->assets_by_id = ( new Asset_Repo() )->find_many( $asset_ids );

		$this->_column_headers = array( $this->get_columns(), array(), array() );
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
	 * Default column.
	 *
	 * @param object $item        Row.
	 * @param string $column_name Column.
	 */
	protected function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'reference':
				return '<code>' . esc_html( Labels::request_reference( (int) $item->id ) ) . '</code>';
			case 'service_tag':
				$asset = $this->assets_by_id[ (int) $item->asset_id ] ?? null;
				$tag   = $asset ? (string) $asset->service_tag : '—';
				if ( ! $asset ) {
					return esc_html( $tag );
				}
				$url = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $asset->id );
				return '<code><a class="itwt-tag-link" href="' . esc_url( $url ) . '">' . esc_html( $tag ) . '</a></code>';
			case 'request_type':
				return esc_html( Labels::request_type( (string) $item->request_type ) );
			case 'contact_name':
			case 'phone':
				return esc_html( (string) $item->{$column_name} );
			case 'created_at':
				return esc_html( Dates::format_datetime( (string) $item->created_at ) );
			case 'status':
				$status = (string) $item->status;
				return '<span class="itwt-badge-status itwt-badge-status--' . esc_attr( $status ) . '">' . esc_html( Labels::request_status( $status ) ) . '</span>';
			case 'actions':
				return $this->actions_cell( $item );
			default:
				return '';
		}
	}

	/**
	 * Open-drawer button with payload.
	 *
	 * @param object $item Request row.
	 */
	private function actions_cell( object $item ): string {
		$asset = $this->assets_by_id[ (int) $item->asset_id ] ?? null;
		$tag   = $asset ? (string) $asset->service_tag : '';
		$payload = wp_json_encode(
			array(
				'id'           => (int) $item->id,
				'reference'    => Labels::request_reference( (int) $item->id ),
				'service_tag'  => $tag,
				'asset_url'    => $asset ? admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $asset->id ) : '',
				'request_type' => Labels::request_type( (string) $item->request_type ),
				'request_key'  => (string) $item->request_type,
				'contact_name' => (string) $item->contact_name,
				'phone'        => (string) $item->phone,
				'detail'       => (string) $item->detail,
				'status'       => (string) $item->status,
				'status_label' => Labels::request_status( (string) $item->status ),
				'created_at'   => Dates::format_datetime( (string) $item->created_at ),
			)
		);

		return '<div class="itwt-row-actions">'
			. sprintf(
				'<button type="button" class="itwt-row-action itwt-open-request" data-request="%s">%s</button>',
				esc_attr( is_string( $payload ) ? $payload : '{}' ),
				esc_html__( 'ดูรายละเอียด', 'it-warranty-tracker' )
			)
			. '</div>';
	}

	/**
	 * Filters live in the page toolbar (itwt-panel__toolbar), not tablenav.
	 *
	 * @param string $which Top/bottom.
	 */
	protected function extra_tablenav( $which ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Intentionally empty — shared toolbar in request-list.php.
	}
}
