<?php
/**
 * Asset list view.
 *
 * @package ITWT
 * @var \ITWT\Admin\Asset_List_Table $table
 * @var array|null                   $import_job
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;

$search     = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$asset_type = isset( $_REQUEST['asset_type'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['asset_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$coverage   = isset( $_REQUEST['coverage'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['coverage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$total      = (int) $table->get_pagination_arg( 'total_items' );
$per_page   = Admin_List::normalize_per_page( (int) $table->get_pagination_arg( 'per_page' ) );
$page       = max( 1, (int) $table->get_pagenum() );
$has_items  = $total > 0;
?>
<div class="wrap itwt-wrap">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'ทรัพย์สิน', 'it-warranty-tracker' ); ?></h1>
		</div>
		<div class="itwt-page-header__actions">
			<a class="itwt-action itwt-action--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&action=new' ) ); ?>"><?php esc_html_e( 'เพิ่มใหม่', 'it-warranty-tracker' ); ?></a>
			<button type="button" class="itwt-action" data-itwt-modal-open="itwt-import-modal"><?php esc_html_e( 'นำเข้า CSV', 'it-warranty-tracker' ); ?></button>
			<a class="itwt-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=itwt_export_csv' ), 'itwt_export_csv' ) ); ?>"><?php esc_html_e( 'Export CSV', 'it-warranty-tracker' ); ?></a>
		</div>
	</header>

	<?php
	if ( ! empty( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'บันทึกแล้ว', 'it-warranty-tracker' ) );
	}
	if ( ! empty( $_GET['import'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'info', __( 'เริ่มนำเข้า CSV แล้ว (ประมวลผลเป็นชุดในพื้นหลัง)', 'it-warranty-tracker' ) );
	}
	if ( ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'error', sanitize_text_field( wp_unslash( (string) $_GET['error'] ) ) );
	}
	if ( is_array( $import_job ) && in_array( $import_job['status'] ?? '', array( 'pending', 'running' ), true ) ) {
		\ITWT\Toast::push(
			'info',
			sprintf(
				/* translators: %d: rows done */
				__( 'กำลังนำเข้า CSV… เสร็จแล้ว %d แถว', 'it-warranty-tracker' ),
				(int) ( $import_job['done'] ?? 0 )
			)
		);
	} elseif ( is_array( $import_job ) && 'done' === ( $import_job['status'] ?? '' ) ) {
		\ITWT\Toast::push(
			'success',
			sprintf(
				/* translators: 1: done, 2: errors */
				__( 'นำเข้าเสร็จ %1$d แถว (ผิดพลาด %2$d)', 'it-warranty-tracker' ),
				(int) ( $import_job['done'] ?? 0 ),
				count( $import_job['errors'] ?? array() )
			)
		);
	}
	?>

	<section class="itwt-panel itwt-list-panel">
		<header class="itwt-panel__head">
			<h2 class="itwt-panel__title"><?php esc_html_e( 'รายการทรัพย์สิน', 'it-warranty-tracker' ); ?></h2>
			<span class="itwt-panel__count"><?php echo esc_html( (string) $total ); ?></span>
		</header>
		<div class="itwt-panel__body">
			<div class="itwt-panel__toolbar">
				<?php if ( $has_items ) : ?>
					<div class="itwt-bulk-bar">
						<label class="screen-reader-text" for="itwt-asset-bulk-action"><?php esc_html_e( 'การดำเนินการแบบกลุ่ม', 'it-warranty-tracker' ); ?></label>
						<select id="itwt-asset-bulk-action" class="itwt-bulk-action" name="action" form="itwt-assets-bulk-form">
							<option value=""><?php esc_html_e( 'การดำเนินการแบบกลุ่ม', 'it-warranty-tracker' ); ?></option>
							<option value="print_labels"><?php esc_html_e( 'พิมพ์สติ๊กเกอร์ที่เลือก', 'it-warranty-tracker' ); ?></option>
						</select>
						<button type="submit" class="itwt-action" form="itwt-assets-bulk-form"><?php esc_html_e( 'ทำรายการ', 'it-warranty-tracker' ); ?></button>
						<span class="itwt-bulk-bar__hint itwt-bulk-selected-hint" hidden></span>
					</div>
				<?php endif; ?>
				<span class="itwt-panel__toolbar-spacer" aria-hidden="true"></span>
				<form method="get" class="itwt-toolbar-filters">
					<input type="hidden" name="page" value="itwt-assets" />
					<input type="hidden" name="per_page" value="<?php echo esc_attr( (string) $per_page ); ?>" />
					<label class="screen-reader-text" for="itwt-filter-asset-type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
					<select name="asset_type" id="itwt-filter-asset-type">
						<option value=""><?php esc_html_e( 'ทุกประเภท', 'it-warranty-tracker' ); ?></option>
						<?php foreach ( \ITWT\Asset_Types::all() as $type ) : ?>
							<option value="<?php echo esc_attr( $type['slug'] ); ?>" <?php selected( $asset_type, $type['slug'] ); ?>><?php echo esc_html( $type['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="itwt-filter-coverage"><?php esc_html_e( 'สถานะคุ้มครอง', 'it-warranty-tracker' ); ?></label>
					<select name="coverage" id="itwt-filter-coverage">
						<option value=""><?php esc_html_e( 'ทุกสถานะคุ้มครอง', 'it-warranty-tracker' ); ?></option>
						<?php foreach ( array( 'covered', 'expiring', 'expired', 'void', 'decommissioned' ) as $c ) : ?>
							<option value="<?php echo esc_attr( $c ); ?>" <?php selected( $coverage, $c ); ?>><?php echo esc_html( \ITWT\Coverage::label( $c ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="itwt-asset-search"><?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?></label>
					<input type="search" id="itwt-asset-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'ค้นหา Service Tag / รุ่น / Serial', 'it-warranty-tracker' ); ?>" />
					<button type="submit" class="itwt-action"><?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?></button>
				</form>
			</div>

			<form method="post" id="itwt-assets-bulk-form" class="itwt-bulk-form itwt-panel__table">
				<?php wp_nonce_field( 'bulk-assets' ); ?>
				<input type="hidden" name="page" value="itwt-assets" />
				<?php if ( '' !== $search ) : ?>
					<input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>" />
				<?php endif; ?>
				<?php if ( '' !== $asset_type ) : ?>
					<input type="hidden" name="asset_type" value="<?php echo esc_attr( $asset_type ); ?>" />
				<?php endif; ?>
				<?php if ( '' !== $coverage ) : ?>
					<input type="hidden" name="coverage" value="<?php echo esc_attr( $coverage ); ?>" />
				<?php endif; ?>
				<?php $table->display(); ?>
			</form>

			<?php
			$list_page  = 'itwt-assets';
			$query_args = array_filter(
				array(
					's'          => $search,
					'asset_type' => $asset_type,
					'coverage'   => $coverage,
				),
				static function ( $value ) {
					return '' !== (string) $value;
				}
			);
			include ITWT_PLUGIN_DIR . 'admin/views/partials/list-footer.php';
			?>
		</div>
	</section>

	<div class="itwt-modal" id="itwt-import-modal" hidden aria-hidden="true">
		<button type="button" class="itwt-modal__backdrop" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>"></button>
		<div class="itwt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-import-modal-title">
			<header class="itwt-modal__header">
				<h2 id="itwt-import-modal-title"><?php esc_html_e( 'นำเข้า CSV', 'it-warranty-tracker' ); ?></h2>
				<button type="button" class="itwt-modal__close" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>">&times;</button>
			</header>
			<form method="post" enctype="multipart/form-data" class="itwt-modal__form">
				<?php wp_nonce_field( 'itwt_import_csv' ); ?>
				<input type="hidden" name="itwt_action" value="import_csv" />
				<div class="itwt-modal__body">
					<p class="description"><?php esc_html_e( 'รองรับไฟล์ CSV สูงสุด 5 MB — ระบบจะ upsert ตาม Service Tag เป็นชุดในพื้นหลัง', 'it-warranty-tracker' ); ?></p>
					<label class="itwt-modal__file" for="itwt-csv-file">
						<span class="itwt-modal__file-label"><?php esc_html_e( 'เลือกไฟล์ CSV', 'it-warranty-tracker' ); ?></span>
						<input type="file" id="itwt-csv-file" name="csv_file" accept=".csv,text/csv" required />
					</label>
				</div>
				<footer class="itwt-modal__footer">
					<button type="button" class="itwt-action" data-itwt-modal-close><?php esc_html_e( 'ยกเลิก', 'it-warranty-tracker' ); ?></button>
					<button type="submit" class="itwt-action itwt-action--primary" name="submit"><?php esc_html_e( 'อัปโหลดและนำเข้า', 'it-warranty-tracker' ); ?></button>
				</footer>
			</form>
		</div>
	</div>
</div>
