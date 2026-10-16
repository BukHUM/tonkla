<?php
/**
 * Shared create/edit customer modal.
 *
 * @package ITWT
 * @var object|null $customer            Prefill row (edit) or null.
 * @var int         $edit_id             Customer id being edited (0 = create).
 * @var string      $modal_title         Visible title.
 * @var string      $customer_return_url Optional admin URL to return to after save.
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Labels;

$customer            = $customer ?? null;
$edit_id             = isset( $edit_id ) ? (int) $edit_id : 0;
$modal_title         = isset( $modal_title ) ? (string) $modal_title : __( 'เพิ่มรายชื่อ', 'it-warranty-tracker' );
$customer_return_url = isset( $customer_return_url ) ? (string) $customer_return_url : '';
$open_edit           = $edit_id > 0;
?>
<div class="itwt-modal" id="itwt-customer-modal" hidden aria-hidden="true">
	<button type="button" class="itwt-modal__backdrop" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>"></button>
	<div class="itwt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-customer-modal-title">
		<header class="itwt-modal__header">
			<h2
				id="itwt-customer-modal-title"
				data-create-label="<?php esc_attr_e( 'เพิ่มรายชื่อ', 'it-warranty-tracker' ); ?>"
				data-edit-label="<?php esc_attr_e( 'แก้ไขรายชื่อ', 'it-warranty-tracker' ); ?>"
			><?php echo esc_html( $modal_title ); ?></h2>
			<button type="button" class="itwt-modal__close" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>">&times;</button>
		</header>
		<form method="post" class="itwt-modal__form" id="itwt-customer-form" action="<?php echo esc_url( admin_url( 'admin.php?page=itwt-customers' ) ); ?>">
			<?php wp_nonce_field( 'itwt_save_customer' ); ?>
			<input type="hidden" name="itwt_action" value="save_customer" />
			<input type="hidden" name="customer_id" id="itwt-customer-id" value="<?php echo esc_attr( (string) $edit_id ); ?>" />
			<?php if ( '' !== $customer_return_url ) : ?>
				<input type="hidden" name="itwt_return" id="itwt-customer-return" value="<?php echo esc_url( $customer_return_url ); ?>" />
			<?php endif; ?>
			<div class="itwt-modal__body">
				<div class="itwt-modal__grid">
					<label for="party_role"><?php esc_html_e( 'บทบาท', 'it-warranty-tracker' ); ?></label>
					<select id="party_role" name="party_role" required>
						<?php
						$current_role = (string) ( $customer->party_role ?? 'customer' );
						foreach ( Labels::party_roles() as $role_key => $role_label ) :
							?>
							<option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $current_role, $role_key ); ?>><?php echo esc_html( $role_label ); ?></option>
						<?php endforeach; ?>
					</select>

					<label for="customer_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
					<select id="customer_type" name="customer_type" required>
						<?php
						$current_type = (string) ( $customer->customer_type ?? 'juristic' );
						foreach ( Labels::customer_types() as $type_key => $type_label ) :
							?>
							<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $current_type, $type_key ); ?>><?php echo esc_html( $type_label ); ?></option>
						<?php endforeach; ?>
					</select>

					<label for="company_name"><?php esc_html_e( 'ชื่อ / ชื่อบริษัท', 'it-warranty-tracker' ); ?></label>
					<input type="text" id="company_name" name="company_name" value="<?php echo esc_attr( (string) ( $customer->company_name ?? '' ) ); ?>" required />

					<label for="tax_id"><?php esc_html_e( 'เลขผู้เสียภาษี / บัตรประชาชน', 'it-warranty-tracker' ); ?></label>
					<input type="text" id="tax_id" name="tax_id" value="<?php echo esc_attr( (string) ( $customer->tax_id ?? '' ) ); ?>" inputmode="numeric" maxlength="20" autocomplete="off" placeholder="<?php esc_attr_e( '13 หลัก (ถ้ามี)', 'it-warranty-tracker' ); ?>" />

					<label for="business_location"><?php esc_html_e( 'ที่ตั้งธุรกิจ', 'it-warranty-tracker' ); ?></label>
					<select id="business_location" name="business_location">
						<?php
						$current_loc = (string) ( $customer->business_location ?? 'thai' );
						foreach ( Labels::business_locations() as $loc_key => $loc_label ) :
							?>
							<option value="<?php echo esc_attr( $loc_key ); ?>" <?php selected( $current_loc, $loc_key ); ?>><?php echo esc_html( $loc_label ); ?></option>
						<?php endforeach; ?>
					</select>

					<label for="address"><?php esc_html_e( 'ที่อยู่', 'it-warranty-tracker' ); ?></label>
					<textarea id="address" name="address" rows="2"><?php echo esc_textarea( (string) ( $customer->address ?? '' ) ); ?></textarea>

					<label for="postal_code"><?php esc_html_e( 'รหัสไปรษณีย์', 'it-warranty-tracker' ); ?></label>
					<input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr( (string) ( $customer->postal_code ?? '' ) ); ?>" />

					<label for="contact_name"><?php esc_html_e( 'ผู้ติดต่อ', 'it-warranty-tracker' ); ?></label>
					<input type="text" id="contact_name" name="contact_name" value="<?php echo esc_attr( (string) ( $customer->contact_name ?? '' ) ); ?>" />

					<label for="phone"><?php esc_html_e( 'เบอร์', 'it-warranty-tracker' ); ?></label>
					<input type="text" id="phone" name="phone" value="<?php echo esc_attr( (string) ( $customer->phone ?? '' ) ); ?>" />

					<label for="email"><?php esc_html_e( 'อีเมล', 'it-warranty-tracker' ); ?></label>
					<input type="email" id="email" name="email" value="<?php echo esc_attr( (string) ( $customer->email ?? '' ) ); ?>" />

					<label for="customer_code" class="itwt-customer-code-label"><?php esc_html_e( 'รหัส', 'it-warranty-tracker' ); ?></label>
					<div class="itwt-customer-code-field">
						<input type="text" id="customer_code" name="customer_code" value="<?php echo esc_attr( (string) ( $customer->customer_code ?? '' ) ); ?>" autocomplete="off" readonly placeholder="<?php echo $open_edit ? '' : esc_attr( 'CUS-YYYY-XXXX' ); ?>" />
						<p class="description" id="itwt-customer-code-hint" data-create-hint="<?php esc_attr_e( 'ระบบสร้างรหัสให้อัตโนมัติเมื่อบันทึก', 'it-warranty-tracker' ); ?>" <?php echo $open_edit ? 'hidden' : ''; ?>><?php esc_html_e( 'ระบบสร้างรหัสให้อัตโนมัติเมื่อบันทึก', 'it-warranty-tracker' ); ?></p>
					</div>
				</div>
			</div>
			<footer class="itwt-modal__footer">
				<button type="button" class="itwt-action" data-itwt-modal-close><?php esc_html_e( 'ยกเลิก', 'it-warranty-tracker' ); ?></button>
				<button type="submit" class="itwt-action itwt-action--primary" name="submit"><?php esc_html_e( 'บันทึก', 'it-warranty-tracker' ); ?></button>
			</footer>
		</form>
	</div>
</div>
