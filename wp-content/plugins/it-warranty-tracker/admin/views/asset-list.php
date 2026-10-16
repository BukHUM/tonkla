<?php
/**
 * Asset list view.
 *
 * @package ITWT
 * @var \ITWT\Admin\Asset_List_Table $table
 * @var array|null                   $import_job
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap itwt-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'ทรัพย์สิน', 'it-warranty-tracker' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&action=new' ) ); ?>" class="page-title-action"><?php esc_html_e( 'เพิ่มใหม่', 'it-warranty-tracker' ); ?></a>
	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=itwt_export_csv' ), 'itwt_export_csv' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'it-warranty-tracker' ); ?></a>
	<hr class="wp-header-end" />

	<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'บันทึกแล้ว', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>
	<?php if ( ! empty( $_GET['import'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'เริ่มนำเข้า CSV แล้ว (ประมวลผลเป็นชุดในพื้นหลัง)', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>
	<?php if ( ! empty( $_GET['error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error"><p><?php echo esc_html( sanitize_text_field( wp_unslash( (string) $_GET['error'] ) ) ); ?></p></div>
	<?php endif; ?>

	<?php if ( is_array( $import_job ) && in_array( $import_job['status'] ?? '', array( 'pending', 'running' ), true ) ) : ?>
		<div class="notice notice-info"><p>
			<?php
			printf(
				/* translators: %d: rows done */
				esc_html__( 'กำลังนำเข้า CSV… เสร็จแล้ว %d แถว', 'it-warranty-tracker' ),
				(int) ( $import_job['done'] ?? 0 )
			);
			?>
		</p></div>
	<?php elseif ( is_array( $import_job ) && 'done' === ( $import_job['status'] ?? '' ) ) : ?>
		<div class="notice notice-success"><p>
			<?php
			printf(
				/* translators: 1: done, 2: errors */
				esc_html__( 'นำเข้าเสร็จ %1$d แถว (ผิดพลาด %2$d)', 'it-warranty-tracker' ),
				(int) ( $import_job['done'] ?? 0 ),
				count( $import_job['errors'] ?? array() )
			);
			?>
		</p></div>
	<?php endif; ?>

	<form method="post" enctype="multipart/form-data" class="itwt-import-form">
		<?php wp_nonce_field( 'itwt_import_csv' ); ?>
		<input type="hidden" name="itwt_action" value="import_csv" />
		<label for="itwt-csv-file"><?php esc_html_e( 'นำเข้า CSV', 'it-warranty-tracker' ); ?></label>
		<input type="file" id="itwt-csv-file" name="csv_file" accept=".csv,text/csv" required />
		<?php submit_button( __( 'อัปโหลด', 'it-warranty-tracker' ), 'secondary', 'submit', false ); ?>
	</form>

	<form method="get">
		<input type="hidden" name="page" value="itwt-assets" />
		<?php
		$table->search_box( __( 'ค้นหา', 'it-warranty-tracker' ), 'itwt-asset' );
		$table->display();
		?>
	</form>
</div>
