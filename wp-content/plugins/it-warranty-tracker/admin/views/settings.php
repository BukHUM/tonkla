<?php
/**
 * Settings view.
 *
 * @package ITWT
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$pages = get_pages( array( 'number' => 100 ) );
$milestones = is_array( $settings['milestones'] ?? null ) ? implode( ',', array_map( 'intval', $settings['milestones'] ) ) : '60,30,7,0';
$asset_types = \ITWT\Asset_Types::all();
?>
<div class="wrap itwt-wrap">
	<h1><?php esc_html_e( 'ตั้งค่า IT Warranty Tracker', 'it-warranty-tracker' ); ?></h1>

	<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'บันทึกการตั้งค่าแล้ว', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'itwt_save_settings' ); ?>
		<input type="hidden" name="itwt_action" value="save_settings" />

		<h2><?php esc_html_e( 'ประเภททรัพย์สิน', 'it-warranty-tracker' ); ?></h2>
		<p class="description"><?php esc_html_e( 'ใช้ในฟอร์มทรัพย์สินและตัวกรอง — slug เป็นภาษาอังกฤษตัวเล็ก (สูงสุด 30 ตัวอักษร) ชื่อที่แสดงแก้ได้', 'it-warranty-tracker' ); ?></p>
		<div id="itwt-asset-types" class="itwt-asset-types">
			<?php foreach ( $asset_types as $i => $type ) : ?>
				<div class="itwt-asset-type-row">
					<label>
						<span class="screen-reader-text"><?php esc_html_e( 'Slug', 'it-warranty-tracker' ); ?></span>
						<input type="text" name="asset_type_slug[<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $type['slug'] ); ?>" placeholder="slug" pattern="[a-z0-9_\-]{1,30}" maxlength="30" required />
					</label>
					<label>
						<span class="screen-reader-text"><?php esc_html_e( 'ชื่อที่แสดง', 'it-warranty-tracker' ); ?></span>
						<input type="text" name="asset_type_label[<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $type['label'] ); ?>" placeholder="<?php esc_attr_e( 'ชื่อที่แสดง', 'it-warranty-tracker' ); ?>" required />
					</label>
					<button type="button" class="button itwt-remove-type" aria-label="<?php esc_attr_e( 'ลบประเภท', 'it-warranty-tracker' ); ?>"><?php esc_html_e( 'ลบ', 'it-warranty-tracker' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" class="button" id="itwt-add-asset-type"><?php esc_html_e( 'เพิ่มประเภท', 'it-warranty-tracker' ); ?></button>
		</p>

		<h2><?php esc_html_e( 'ทั่วไป', 'it-warranty-tracker' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="support_phone"><?php esc_html_e( 'เบอร์ศูนย์บริการ', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="support_phone" name="support_phone" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['support_phone'] ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="logo_attachment_id"><?php esc_html_e( 'Logo Attachment ID', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="number" id="logo_attachment_id" name="logo_attachment_id" value="<?php echo esc_attr( (string) (int) ( $settings['logo_attachment_id'] ?? 0 ) ); ?>" min="0" /></td>
			</tr>
			<tr>
				<th><label for="lookup_page_id"><?php esc_html_e( 'หน้าเช็คประกัน', 'it-warranty-tracker' ); ?></label></th>
				<td>
					<select id="lookup_page_id" name="lookup_page_id">
						<option value="0"><?php esc_html_e( '— เลือกหน้า —', 'it-warranty-tracker' ); ?></option>
						<?php foreach ( $pages as $p ) : ?>
							<option value="<?php echo esc_attr( (string) (int) $p->ID ); ?>" <?php selected( (int) ( $settings['lookup_page_id'] ?? 0 ), (int) $p->ID ); ?>>
								<?php echo esc_html( $p->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'หน้าที่มี shortcode [itwt_warranty_lookup] — ใช้ใน QR ห้ามเปลี่ยน URL ภายหลัง', 'it-warranty-tracker' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="expiring_days"><?php esc_html_e( 'วันใกล้หมด (expiring)', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="number" id="expiring_days" name="expiring_days" value="<?php echo esc_attr( (string) (int) ( $settings['expiring_days'] ?? 30 ) ); ?>" min="1" max="365" /></td>
			</tr>
			<tr>
				<th><label for="milestones"><?php esc_html_e( 'Milestones แจ้งเตือน (วัน)', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="milestones" name="milestones" class="regular-text" value="<?php echo esc_attr( $milestones ); ?>" />
					<p class="description"><?php esc_html_e( 'คั่นด้วยจุลภาค เช่น 60,30,7,0', 'it-warranty-tracker' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="request_retention_months"><?php esc_html_e( 'เก็บคำขอ (เดือน)', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="number" id="request_retention_months" name="request_retention_months" value="<?php echo esc_attr( (string) (int) ( $settings['request_retention_months'] ?? 24 ) ); ?>" min="1" max="120" /></td>
			</tr>
			<tr>
				<th><label for="line_oa_id"><?php esc_html_e( 'LINE OA ID', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="line_oa_id" name="line_oa_id" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['line_oa_id'] ?? '' ) ); ?>" placeholder="@xxxx" /></td>
			</tr>
			<tr>
				<th><label for="line_monthly_quota"><?php esc_html_e( 'โควตา LINE / เดือน', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="number" id="line_monthly_quota" name="line_monthly_quota" value="<?php echo esc_attr( (string) (int) ( $settings['line_monthly_quota'] ?? 300 ) ); ?>" min="1" /></td>
			</tr>
			<tr>
				<th><label for="line_customer_reserve"><?php esc_html_e( 'สงวนโควตาให้ลูกค้า', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="number" id="line_customer_reserve" name="line_customer_reserve" value="<?php echo esc_attr( (string) (int) ( $settings['line_customer_reserve'] ?? 150 ) ); ?>" min="0" /></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'กลุ่มช่าง LINE', 'it-warranty-tracker' ); ?></th>
				<td>
					<code><?php echo esc_html( (string) ( $settings['line_tech_group_id'] ?? '—' ) ); ?></code>
					<?php if ( ! empty( $settings['line_pending_group_id'] ) ) : ?>
						<p>
							<?php esc_html_e( 'พบกลุ่มรอยืนยัน:', 'it-warranty-tracker' ); ?>
							<code><?php echo esc_html( (string) $settings['line_pending_group_id'] ); ?></code>
						</p>
					<?php endif; ?>
					<p class="description"><?php esc_html_e( 'Secret/Token ใส่ใน wp-config.php เป็น ITWT_LINE_CHANNEL_SECRET และ ITWT_LINE_CHANNEL_TOKEN', 'it-warranty-tracker' ); ?></p>
					<p class="description"><?php echo esc_html( rest_url( 'itwt/v1/line/webhook' ) ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="pdpa_consent_text"><?php esc_html_e( 'ข้อความยินยอม (ฟอร์ม)', 'it-warranty-tracker' ); ?></label></th>
				<td><textarea id="pdpa_consent_text" name="pdpa_consent_text" class="large-text" rows="3"><?php echo esc_textarea( (string) ( $settings['pdpa_consent_text'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="line_pdpa_text"><?php esc_html_e( 'ข้อความ PDPA (LINE)', 'it-warranty-tracker' ); ?></label></th>
				<td><textarea id="line_pdpa_text" name="line_pdpa_text" class="large-text" rows="3"><?php echo esc_textarea( (string) ( $settings['line_pdpa_text'] ?? '' ) ); ?></textarea></td>
			</tr>
		</table>
		<?php submit_button( __( 'บันทึกการตั้งค่า', 'it-warranty-tracker' ) ); ?>
	</form>

	<?php if ( ! empty( $settings['line_pending_group_id'] ) ) : ?>
		<form method="post">
			<?php wp_nonce_field( 'itwt_confirm_tech_group' ); ?>
			<input type="hidden" name="itwt_action" value="confirm_tech_group" />
			<?php submit_button( __( 'ยืนยันกลุ่มนี้เป็นกลุ่มช่าง', 'it-warranty-tracker' ), 'secondary' ); ?>
		</form>
	<?php endif; ?>
</div>
