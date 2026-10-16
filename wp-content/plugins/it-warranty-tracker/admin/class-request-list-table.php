<?php
/**
 * Support requests WP_List_Table.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Admin;

defined( 'ABSPATH' ) || exit;

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
			'id'           => __( 'ID', 'it-warranty-tracker' ),
			'service_tag'  => __( 'Service Tag', 'it-warranty-tracker' ),
			'request_type' => __( 'ประเภท', 'it-warranty-tracker' ),
			'contact_name' => __( 'ผู้ติดต่อ', 'it-warranty-tracker' ),
			'phone'        => __( 'เบอร์', 'it-warranty-tracker' ),
			'status'       => __( 'สถานะ', 'it-warranty-tracker' ),
			'created_at'   => __( 'วันที่', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items(): void {
		$per_page = 20;
		$page     = $this->get_pagenum();
		$status   = isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$result = ( new Support_Request_Repo() )->list(
			array(
				'status'   => $status,
				'page'     => $page,
				'per_page' => $per_page,
			)
		);

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
	 * Default column.
	 *
	 * @param object $item        Row.
	 * @param string $column_name Column.
	 */
	protected function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'id':
				return esc_html( (string) (int) $item->id );
			case 'service_tag':
				$asset = ( new Asset_Repo() )->find( (int) $item->asset_id );
				$tag   = $asset ? (string) $asset->service_tag : '—';
				$url   = $asset ? admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $asset->id ) : '#';
				return '<a href="' . esc_url( $url ) . '">' . esc_html( $tag ) . '</a>';
			case 'request_type':
				return 'renew_ma' === $item->request_type
					? esc_html__( 'ขอต่อ MA', 'it-warranty-tracker' )
					: esc_html__( 'แจ้งซ่อม', 'it-warranty-tracker' );
			case 'contact_name':
			case 'phone':
			case 'created_at':
				return esc_html( (string) $item->{$column_name} );
			case 'status':
				return $this->status_form( $item );
			default:
				return '';
		}
	}

	/**
	 * Status update form.
	 *
	 * @param object $item Request row.
	 */
	private function status_form( object $item ): string {
		ob_start();
		?>
		<form method="post" class="itwt-inline-status">
			<?php wp_nonce_field( 'itwt_update_request' ); ?>
			<input type="hidden" name="itwt_action" value="update_request_status" />
			<input type="hidden" name="request_id" value="<?php echo esc_attr( (string) (int) $item->id ); ?>" />
			<select name="status">
				<?php foreach ( array( 'new', 'in_progress', 'done', 'spam' ) as $st ) : ?>
					<option value="<?php echo esc_attr( $st ); ?>" <?php selected( $item->status, $st ); ?>><?php echo esc_html( $st ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'บันทึก', 'it-warranty-tracker' ), 'small', 'submit', false ); ?>
		</form>
		<details class="itwt-request-detail">
			<summary><?php esc_html_e( 'รายละเอียด', 'it-warranty-tracker' ); ?></summary>
			<p><?php echo esc_html( (string) $item->detail ); ?></p>
		</details>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Filters.
	 *
	 * @param string $which Top/bottom.
	 */
	protected function extra_tablenav( $which ): void {
		if ( 'top' !== $which ) {
			return;
		}
		$status = isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="alignleft actions">
			<select name="status">
				<option value=""><?php esc_html_e( 'ทุกสถานะ', 'it-warranty-tracker' ); ?></option>
				<?php foreach ( array( 'new', 'in_progress', 'done', 'spam' ) as $st ) : ?>
					<option value="<?php echo esc_attr( $st ); ?>" <?php selected( $status, $st ); ?>><?php echo esc_html( $st ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'กรอง', 'it-warranty-tracker' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}
}
