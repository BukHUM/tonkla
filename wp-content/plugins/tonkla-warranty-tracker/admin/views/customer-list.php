<?php
/**
 * Customer / partner list with add/edit + import modal.
 *
 * @package ITWT
 * @var array            $result
 * @var object|null      $customer
 * @var string           $search
 * @var string           $party_role
 * @var int              $page
 * @var int              $per_page
 * @var array|false|null $import_result
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Labels;
use ITWT\Repositories\Customer_Repo;

$total    = (int) ( $result['total'] ?? 0 );
$per_page = Customer_Repo::normalize_per_page( isset( $per_page ) ? (int) $per_page : 20 );
$edit_id     = (int) ( $customer->id ?? 0 );
$open_edit   = $edit_id > 0;
$party_role  = isset( $party_role ) ? (string) $party_role : '';
$modal_title = $open_edit
	? __( 'แก้ไขรายชื่อ', 'tonkla-warranty-tracker' )
	: __( 'เพิ่มรายชื่อ', 'tonkla-warranty-tracker' );
?>
<div class="wrap itwt-wrap" <?php echo $open_edit ? 'data-itwt-open-modal="itwt-customer-modal"' : ''; ?>>
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'tonkla-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'ลูกค้า / คู่ค้า', 'tonkla-warranty-tracker' ); ?></h1>
		</div>
		<div class="itwt-page-header__actions">
			<button type="button" class="itwt-action" data-itwt-modal-open="itwt-contact-import-modal">
				<?php esc_html_e( 'นำเข้า Excel', 'tonkla-warranty-tracker' ); ?>
			</button>
			<button type="button" class="itwt-action itwt-action--primary" id="itwt-open-customer-modal" data-itwt-modal-open="itwt-customer-modal" data-mode="create">
				<?php esc_html_e( 'เพิ่มรายชื่อ', 'tonkla-warranty-tracker' ); ?>
			</button>
		</div>
	</header>

	<?php
	$flash = get_transient( 'itwt_customer_notice_' . get_current_user_id() );
	$had_flash = false;
	if ( is_array( $flash ) && ! empty( $flash['message'] ) ) {
		$had_flash = true;
		delete_transient( 'itwt_customer_notice_' . get_current_user_id() );
		$toast_type = ( 'error' === ( $flash['type'] ?? '' ) ) ? 'error' : 'success';
		\ITWT\Toast::push( $toast_type, (string) $flash['message'] );
	}
	if ( is_array( $import_result ) ) {
		delete_transient( 'itwt_contact_import_' . get_current_user_id() );
		if ( empty( $import_result['ok'] ) ) {
			\ITWT\Toast::push( 'error', (string) ( $import_result['message'] ?? __( 'นำเข้าไม่สำเร็จ', 'tonkla-warranty-tracker' ) ) );
		} else {
			\ITWT\Toast::push(
				'success',
				sprintf(
					/* translators: 1: created, 2: updated, 3: skipped */
					__( 'นำเข้าสำเร็จ — เพิ่ม %1$d อัปเดต %2$d ข้าม %3$d', 'tonkla-warranty-tracker' ),
					(int) ( $import_result['created'] ?? 0 ),
					(int) ( $import_result['updated'] ?? 0 ),
					(int) ( $import_result['skipped'] ?? 0 )
				)
			);
			if ( ! empty( $import_result['errors'] ) && is_array( $import_result['errors'] ) ) {
				foreach ( array_slice( $import_result['errors'], 0, 5 ) as $err ) {
					\ITWT\Toast::push( 'warning', (string) $err );
				}
			}
		}
	} elseif ( ! $had_flash && ( ! empty( $_GET['updated'] ) || ! empty( $_GET['deleted'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'ดำเนินการสำเร็จ', 'tonkla-warranty-tracker' ) );
	} elseif ( ! $had_flash && ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'error', __( 'ดำเนินการไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
	}
	?>

	<section class="itwt-panel">
		<header class="itwt-panel__head">
			<h2 class="itwt-panel__title"><?php esc_html_e( 'รายชื่อ', 'tonkla-warranty-tracker' ); ?></h2>
			<span class="itwt-panel__count"><?php echo esc_html( (string) $total ); ?></span>
		</header>
		<div class="itwt-panel__body">
			<div class="itwt-panel__toolbar">
				<?php if ( ! empty( $result['items'] ) ) : ?>
					<div class="itwt-bulk-bar">
						<label class="screen-reader-text" for="itwt-bulk-action"><?php esc_html_e( 'การดำเนินการแบบกลุ่ม', 'tonkla-warranty-tracker' ); ?></label>
						<select id="itwt-bulk-action" class="itwt-bulk-action" name="bulk_action" form="itwt-customers-bulk-form">
							<option value=""><?php esc_html_e( 'การดำเนินการแบบกลุ่ม', 'tonkla-warranty-tracker' ); ?></option>
							<option value="set_customer"><?php esc_html_e( 'ตั้งเป็นลูกค้า', 'tonkla-warranty-tracker' ); ?></option>
							<option value="set_partner"><?php esc_html_e( 'ตั้งเป็นคู่ค้า', 'tonkla-warranty-tracker' ); ?></option>
							<option value="set_both"><?php esc_html_e( 'ตั้งเป็นลูกค้าและคู่ค้า', 'tonkla-warranty-tracker' ); ?></option>
							<option value="delete"><?php esc_html_e( 'ลบที่เลือก', 'tonkla-warranty-tracker' ); ?></option>
						</select>
						<button type="submit" class="itwt-action" id="itwt-bulk-apply" form="itwt-customers-bulk-form"><?php esc_html_e( 'ทำรายการ', 'tonkla-warranty-tracker' ); ?></button>
						<span class="itwt-bulk-bar__hint itwt-bulk-selected-hint" id="itwt-bulk-selected-hint" hidden></span>
					</div>
				<?php endif; ?>
				<span class="itwt-panel__toolbar-spacer" aria-hidden="true"></span>
					<form method="get" class="itwt-toolbar-filters">
					<input type="hidden" name="page" value="itwt-customers" />
					<input type="hidden" name="per_page" value="<?php echo esc_attr( (string) $per_page ); ?>" />
					<label class="screen-reader-text" for="party-role-filter"><?php esc_html_e( 'บทบาท', 'tonkla-warranty-tracker' ); ?></label>
					<select id="party-role-filter" name="party_role">
						<option value=""><?php esc_html_e( 'ทุกบทบาท', 'tonkla-warranty-tracker' ); ?></option>
						<?php foreach ( Labels::party_roles() as $role_key => $role_label ) : ?>
							<option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $party_role, $role_key ); ?>><?php echo esc_html( $role_label ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="customer-search"><?php esc_html_e( 'ค้นหา', 'tonkla-warranty-tracker' ); ?></label>
					<input type="search" id="customer-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'ค้นหารหัส ชื่อ หรือเลขผู้เสียภาษี', 'tonkla-warranty-tracker' ); ?>" />
					<button type="submit" class="itwt-action"><?php esc_html_e( 'ค้นหา', 'tonkla-warranty-tracker' ); ?></button>
				</form>
			</div>

			<form method="post" id="itwt-customers-bulk-form" class="itwt-bulk-form">
				<?php wp_nonce_field( 'itwt_bulk_customers' ); ?>
				<input type="hidden" name="itwt_action" value="bulk_customers" />

				<table class="itwt-table">
					<thead>
						<tr>
							<th class="itwt-check-col" scope="col">
								<label class="screen-reader-text" for="itwt-cb-select-all"><?php esc_html_e( 'เลือกทั้งหมด', 'tonkla-warranty-tracker' ); ?></label>
								<input type="checkbox" id="itwt-cb-select-all" class="itwt-cb-select-all" <?php disabled( empty( $result['items'] ) ); ?> />
							</th>
							<th><?php esc_html_e( 'รหัส', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'ชื่อ', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'บทบาท', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'ประเภท', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'เลขผู้เสียภาษี / บัตรประชาชน', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'ผู้ติดต่อ', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'เบอร์', 'tonkla-warranty-tracker' ); ?></th>
							<th><?php esc_html_e( 'จัดการ', 'tonkla-warranty-tracker' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $result['items'] ) ) : ?>
							<tr class="itwt-table__empty-row">
								<td colspan="9">
									<div class="itwt-empty">
										<p class="itwt-empty__title"><?php esc_html_e( 'ยังไม่มีรายชื่อ', 'tonkla-warranty-tracker' ); ?></p>
										<p class="itwt-empty__hint"><?php esc_html_e( 'เพิ่มรายชื่อ หรือนำเข้าจากไฟล์ Excel สมุดรายชื่อ', 'tonkla-warranty-tracker' ); ?></p>
										<p>
											<button type="button" class="itwt-action" data-itwt-modal-open="itwt-contact-import-modal"><?php esc_html_e( 'นำเข้า Excel', 'tonkla-warranty-tracker' ); ?></button>
											<button type="button" class="itwt-action itwt-action--primary" data-itwt-modal-open="itwt-customer-modal" data-mode="create"><?php esc_html_e( 'เพิ่มรายชื่อ', 'tonkla-warranty-tracker' ); ?></button>
										</p>
									</div>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $result['items'] as $row ) : ?>
								<?php
								$row_type = (string) ( $row->customer_type ?? 'juristic' );
								$row_role = (string) ( $row->party_role ?? 'customer' );
								$row_tax  = (string) ( $row->tax_id ?? '' );
								$row_loc  = (string) ( $row->business_location ?? 'thai' );
								$row_id   = (int) $row->id;
								?>
								<tr>
									<th class="itwt-check-col" scope="row">
										<label class="screen-reader-text" for="cb-customer-<?php echo esc_attr( (string) $row_id ); ?>">
											<?php
											printf(
												/* translators: %s: company name */
												esc_html__( 'เลือก %s', 'tonkla-warranty-tracker' ),
												esc_html( (string) $row->company_name )
											);
											?>
										</label>
										<input type="checkbox" class="itwt-cb-row" id="cb-customer-<?php echo esc_attr( (string) $row_id ); ?>" name="customer_ids[]" value="<?php echo esc_attr( (string) $row_id ); ?>" />
									</th>
									<td><code><?php echo esc_html( (string) $row->customer_code ); ?></code></td>
									<td><?php echo esc_html( (string) $row->company_name ); ?></td>
									<td><?php echo esc_html( Labels::party_role( $row_role ) ); ?></td>
									<td><?php echo esc_html( Labels::customer_type( $row_type ) ); ?></td>
									<td><?php echo '' !== $row_tax ? esc_html( $row_tax ) : '—'; ?></td>
									<td><?php echo esc_html( (string) $row->contact_name ); ?></td>
									<td><?php echo esc_html( (string) $row->phone ); ?></td>
									<td class="itwt-table__actions">
										<div class="itwt-row-actions">
											<button
												type="button"
												class="itwt-row-action itwt-edit-customer"
												data-itwt-modal-open="itwt-customer-modal"
												data-mode="edit"
												data-id="<?php echo esc_attr( (string) $row_id ); ?>"
												data-code="<?php echo esc_attr( (string) $row->customer_code ); ?>"
												data-company="<?php echo esc_attr( (string) $row->company_name ); ?>"
												data-role="<?php echo esc_attr( $row_role ); ?>"
												data-type="<?php echo esc_attr( $row_type ); ?>"
												data-tax="<?php echo esc_attr( $row_tax ); ?>"
												data-location="<?php echo esc_attr( $row_loc ); ?>"
												data-address="<?php echo esc_attr( (string) ( $row->address ?? '' ) ); ?>"
												data-postal="<?php echo esc_attr( (string) ( $row->postal_code ?? '' ) ); ?>"
												data-contact="<?php echo esc_attr( (string) $row->contact_name ); ?>"
												data-phone="<?php echo esc_attr( (string) $row->phone ); ?>"
												data-email="<?php echo esc_attr( (string) $row->email ); ?>"
											><?php esc_html_e( 'แก้ไข', 'tonkla-warranty-tracker' ); ?></button>
											<button
												type="submit"
												form="itwt-delete-customer-<?php echo esc_attr( (string) $row_id ); ?>"
												class="itwt-row-action itwt-row-action--danger"
												data-itwt-confirm="delete"
											><?php esc_html_e( 'ลบ', 'tonkla-warranty-tracker' ); ?></button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</form>

			<?php if ( ! empty( $result['items'] ) ) : ?>
				<?php foreach ( $result['items'] as $row ) : ?>
					<form method="post" id="itwt-delete-customer-<?php echo esc_attr( (string) (int) $row->id ); ?>" class="itwt-hidden-form" hidden>
						<?php wp_nonce_field( 'itwt_delete_customer' ); ?>
						<input type="hidden" name="itwt_action" value="delete_customer" />
						<input type="hidden" name="customer_id" value="<?php echo esc_attr( (string) (int) $row->id ); ?>" />
					</form>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php
			$list_page  = 'itwt-customers';
			$query_args = array_filter(
				array(
					's'          => $search,
					'party_role' => $party_role,
				),
				static function ( $value ) {
					return '' !== (string) $value;
				}
			);
			include ITWT_PLUGIN_DIR . 'admin/views/partials/list-footer.php';
			?>
		</div>
	</section>

	<?php
	$customer_return_url = '';
	include ITWT_PLUGIN_DIR . 'admin/views/partials/customer-modal.php';
	?>

	<div class="itwt-modal" id="itwt-contact-import-modal" hidden aria-hidden="true">
		<button type="button" class="itwt-modal__backdrop" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'tonkla-warranty-tracker' ); ?>"></button>
		<div class="itwt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-contact-import-title">
			<header class="itwt-modal__header">
				<h2 id="itwt-contact-import-title"><?php esc_html_e( 'นำเข้าสมุดรายชื่อ', 'tonkla-warranty-tracker' ); ?></h2>
				<button type="button" class="itwt-modal__close" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'tonkla-warranty-tracker' ); ?>">&times;</button>
			</header>
			<form method="post" enctype="multipart/form-data" class="itwt-modal__form" action="<?php echo esc_url( admin_url( 'admin.php?page=itwt-customers' ) ); ?>">
				<?php wp_nonce_field( 'itwt_import_contacts' ); ?>
				<input type="hidden" name="itwt_action" value="import_contacts" />
				<div class="itwt-modal__body">
					<p class="description"><?php esc_html_e( 'รองรับไฟล์ Excel (.xlsx) หรือ CSV จาก Peak สมุดรายชื่อ — คอลัมน์ “ประเภท” ลูกค้า / ผู้จำหน่าย จะถูกบันทึกเป็นลูกค้า / คู่ค้า', 'tonkla-warranty-tracker' ); ?></p>
					<label class="itwt-modal__file" for="itwt-contact-file">
						<span><?php esc_html_e( 'เลือกไฟล์', 'tonkla-warranty-tracker' ); ?></span>
						<input type="file" id="itwt-contact-file" name="contact_file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required />
					</label>
				</div>
				<footer class="itwt-modal__footer">
					<button type="button" class="itwt-action" data-itwt-modal-close><?php esc_html_e( 'ยกเลิก', 'tonkla-warranty-tracker' ); ?></button>
					<button type="submit" class="itwt-action itwt-action--primary" name="submit"><?php esc_html_e( 'นำเข้า', 'tonkla-warranty-tracker' ); ?></button>
				</footer>
			</form>
		</div>
	</div>
</div>
