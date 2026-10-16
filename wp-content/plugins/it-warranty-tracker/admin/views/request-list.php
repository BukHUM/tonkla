<?php
/**
 * Support request list view.
 *
 * @package ITWT
 * @var \ITWT\Admin\Request_List_Table $table
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;
use ITWT\Labels;

$status    = isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$search    = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$total     = (int) $table->get_pagination_arg( 'total_items' );
$per_page  = Admin_List::normalize_per_page( (int) $table->get_pagination_arg( 'per_page' ) );
$page      = max( 1, (int) $table->get_pagenum() );
?>
<div class="wrap itwt-wrap">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'คำขอแจ้งซ่อม / ต่อ MA', 'it-warranty-tracker' ); ?></h1>
		</div>
	</header>
	<?php
	if ( ! empty( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'อัปเดตสถานะแล้ว', 'it-warranty-tracker' ) );
	}
	?>

	<section class="itwt-panel itwt-list-panel">
		<header class="itwt-panel__head">
			<h2 class="itwt-panel__title"><?php esc_html_e( 'รายการคำขอ', 'it-warranty-tracker' ); ?></h2>
			<span class="itwt-panel__count"><?php echo esc_html( (string) $total ); ?></span>
		</header>
		<div class="itwt-panel__body">
			<div class="itwt-panel__toolbar">
				<span class="itwt-panel__toolbar-spacer" aria-hidden="true"></span>
				<form method="get" class="itwt-toolbar-filters">
					<input type="hidden" name="page" value="itwt-requests" />
					<input type="hidden" name="per_page" value="<?php echo esc_attr( (string) $per_page ); ?>" />
					<label class="screen-reader-text" for="itwt-filter-request-status"><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></label>
					<select name="status" id="itwt-filter-request-status">
						<option value=""><?php esc_html_e( 'ทุกสถานะ', 'it-warranty-tracker' ); ?></option>
						<?php foreach ( Labels::request_statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="itwt-request-search"><?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?></label>
					<input type="search" id="itwt-request-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'ค้นหาผู้ติดต่อ / เบอร์ / รายละเอียด', 'it-warranty-tracker' ); ?>" />
					<button type="submit" class="itwt-action"><?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?></button>
				</form>
			</div>

			<div class="itwt-panel__table">
				<?php $table->display(); ?>
			</div>

			<?php
			$list_page  = 'itwt-requests';
			$query_args = array_filter(
				array(
					'status' => $status,
					's'      => $search,
				),
				static function ( $value ) {
					return '' !== (string) $value;
				}
			);
			include ITWT_PLUGIN_DIR . 'admin/views/partials/list-footer.php';
			?>
		</div>
	</section>

	<div class="itwt-drawer" id="itwt-request-drawer" hidden>
		<div class="itwt-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-drawer-title">
			<header class="itwt-drawer__header">
				<h2 id="itwt-drawer-title"><?php esc_html_e( 'รายละเอียดคำขอ', 'it-warranty-tracker' ); ?></h2>
				<button type="button" class="button-link itwt-drawer__close" id="itwt-drawer-close" aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>">&times;</button>
			</header>
			<div class="itwt-drawer__body">
				<p><strong><?php esc_html_e( 'รหัสอ้างอิง', 'it-warranty-tracker' ); ?>:</strong> <code id="itwt-d-ref"></code></p>
				<p><strong><?php esc_html_e( 'Service Tag', 'it-warranty-tracker' ); ?>:</strong> <a id="itwt-d-tag" href="#"></a></p>
				<p><strong><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?>:</strong> <span id="itwt-d-type"></span></p>
				<p><strong><?php esc_html_e( 'ผู้ติดต่อ', 'it-warranty-tracker' ); ?>:</strong> <span id="itwt-d-contact"></span></p>
				<p><strong><?php esc_html_e( 'เบอร์', 'it-warranty-tracker' ); ?>:</strong> <span id="itwt-d-phone"></span></p>
				<p><strong><?php esc_html_e( 'วันที่', 'it-warranty-tracker' ); ?>:</strong> <span id="itwt-d-date"></span></p>
				<p><strong><?php esc_html_e( 'รายละเอียด', 'it-warranty-tracker' ); ?></strong></p>
				<div class="itwt-drawer__detail" id="itwt-d-detail"></div>
			</div>
			<footer class="itwt-drawer__footer">
				<form method="post" class="itwt-drawer__status">
					<?php wp_nonce_field( 'itwt_update_request' ); ?>
					<input type="hidden" name="itwt_action" value="update_request_status" />
					<input type="hidden" name="request_id" id="itwt-d-id" value="" />
					<label for="itwt-d-status"><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></label>
					<select name="status" id="itwt-d-status">
						<?php foreach ( Labels::request_statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php submit_button( __( 'บันทึกสถานะ', 'it-warranty-tracker' ), 'primary', 'submit', false ); ?>
				</form>
			</footer>
		</div>
		<button type="button" class="itwt-drawer__backdrop" id="itwt-drawer-backdrop" aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>"></button>
	</div>
</div>
