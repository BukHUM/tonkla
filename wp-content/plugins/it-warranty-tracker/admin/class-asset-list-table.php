<?php
/**
 * Assets WP_List_Table.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Admin;

defined( 'ABSPATH' ) || exit;

use ITWT\Asset_Types;
use ITWT\Coverage;
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
	 * Prepare items.
	 */
	public function prepare_items(): void {
		$per_page = 20;
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
	 * Checkbox column.
	 *
	 * @param object $item Row.
	 */
	protected function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="asset_ids[]" value="%d" />', (int) $item->id );
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
		$edit  = admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $item->id );
		$print = admin_url( 'admin.php?page=itwt-print-label&ids=' . (int) $item->id );
		$actions = array(
			'edit'  => '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'แก้ไข', 'it-warranty-tracker' ) . '</a>',
			'print' => '<a href="' . esc_url( $print ) . '">' . esc_html__( 'พิมพ์สติ๊กเกอร์', 'it-warranty-tracker' ) . '</a>',
		);
		return '<strong><a href="' . esc_url( $edit ) . '">' . esc_html( (string) $item->service_tag ) . '</a></strong>' . $this->row_actions( $actions );
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
			case 'status':
				return esc_html( (string) $item->{$column_name} );
			case 'asset_type':
				return esc_html( Asset_Types::label( (string) $item->asset_type ) );
			case 'coverage':
				$contracts = ( new Contract_Repo() )->for_asset( (int) $item->id );
				$cov       = Coverage::compute( (string) $item->status, $contracts );
				return esc_html( Coverage::label( $cov['status'], $cov['days_remaining'] ) );
			default:
				return '';
		}
	}

	/**
	 * Extra table nav filters.
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ): void {
		if ( 'top' !== $which ) {
			return;
		}
		$asset_type = isset( $_REQUEST['asset_type'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['asset_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$coverage   = isset( $_REQUEST['coverage'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['coverage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="alignleft actions">
			<select name="asset_type">
				<option value=""><?php esc_html_e( 'ทุกประเภท', 'it-warranty-tracker' ); ?></option>
				<?php foreach ( Asset_Types::all() as $type ) : ?>
					<option value="<?php echo esc_attr( $type['slug'] ); ?>" <?php selected( $asset_type, $type['slug'] ); ?>><?php echo esc_html( $type['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="coverage">
				<option value=""><?php esc_html_e( 'ทุกสถานะคุ้มครอง', 'it-warranty-tracker' ); ?></option>
				<?php
				foreach ( array( 'covered', 'expiring', 'expired', 'void', 'decommissioned' ) as $c ) :
					?>
					<option value="<?php echo esc_attr( $c ); ?>" <?php selected( $coverage, $c ); ?>><?php echo esc_html( $c ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'กรอง', 'it-warranty-tracker' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}
}
