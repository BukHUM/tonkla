<?php
/**
 * Settings view (tabbed).
 *
 * @package ITWT
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$pages       = get_pages( array( 'number' => 100 ) );
$milestones  = is_array( $settings['milestones'] ?? null ) ? implode( ',', array_map( 'intval', $settings['milestones'] ) ) : '60,30,7,0';
$asset_types = \ITWT\Asset_Types::all();
$logo_id     = (int) ( $settings['logo_attachment_id'] ?? 0 );
$logo_url    = $logo_id > 0 ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';

$display_toggles = array(
	'show_masked_serial'  => __( 'แสดง Serial แบบปิดบางส่วน', 'it-warranty-tracker' ),
	'show_purchase_date'  => __( 'แสดงวันซื้อ', 'it-warranty-tracker' ),
	'show_warranty_end'   => __( 'แสดงวันหมดประกัน / วันสิ้นสุดความคุ้มครอง', 'it-warranty-tracker' ),
	'show_coverage_bar'   => __( 'แสดงแถบวันคงเหลือ', 'it-warranty-tracker' ),
	'show_specs'          => __( 'แสดงสเปกที่ตั้งเป็นสาธารณะ', 'it-warranty-tracker' ),
	'show_contracts'      => __( 'แสดงรายการสัญญา', 'it-warranty-tracker' ),
	'show_sla'            => __( 'แสดงเงื่อนไขบริการ / SLA (เช่น On-Site)', 'it-warranty-tracker' ),
	'show_service_logs'   => __( 'แสดงประวัติบริการ (เฉพาะสรุปสาธารณะ)', 'it-warranty-tracker' ),
	'show_line_button'    => __( 'แสดงปุ่มรับแจ้งเตือนประกันทาง LINE', 'it-warranty-tracker' ),
	'show_support_button' => __( 'แสดงปุ่มแจ้งซ่อม / ขอต่อ MA', 'it-warranty-tracker' ),
);
$portal_theme = sanitize_key( (string) ( $settings['portal_theme'] ?? 'soft' ) );
if ( ! in_array( $portal_theme, array( 'soft', 'minimal', 'dense' ), true ) ) {
	$portal_theme = 'soft';
}

$tabs = array(
	'general'  => __( 'ทั่วไป', 'it-warranty-tracker' ),
	'types'    => __( 'ประเภททรัพย์สิน', 'it-warranty-tracker' ),
	'portal'   => __( 'หน้าเช็คประกัน', 'it-warranty-tracker' ),
	'alerts'   => __( 'แจ้งเตือน / เก็บข้อมูล', 'it-warranty-tracker' ),
	'line'     => __( 'LINE', 'it-warranty-tracker' ),
);
$first_tab = 'general';
?>
<div class="wrap itwt-wrap">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'ตั้งค่า', 'it-warranty-tracker' ); ?></h1>
		</div>
		<div class="itwt-page-header__actions">
			<button type="submit" form="itwt-settings-form" class="itwt-action itwt-action--primary"><?php esc_html_e( 'บันทึกการตั้งค่า', 'it-warranty-tracker' ); ?></button>
		</div>
	</header>

	<?php
	if ( ! empty( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'บันทึกการตั้งค่าแล้ว', 'it-warranty-tracker' ) );
	}
	?>

	<section class="itwt-panel itwt-settings-shell">
		<div class="itwt-tabs" role="tablist" aria-label="<?php esc_attr_e( 'หมวดการตั้งค่า', 'it-warranty-tracker' ); ?>">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<button
					type="button"
					class="itwt-tab"
					role="tab"
					id="itwt-tab-btn-<?php echo esc_attr( $slug ); ?>"
					aria-controls="itwt-panel-<?php echo esc_attr( $slug ); ?>"
					aria-selected="<?php echo $slug === $first_tab ? 'true' : 'false'; ?>"
					tabindex="<?php echo $slug === $first_tab ? '0' : '-1'; ?>"
					data-tab="<?php echo esc_attr( $slug ); ?>"
				><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>

		<form method="post" id="itwt-settings-form" class="itwt-settings-form">
			<?php wp_nonce_field( 'itwt_save_settings' ); ?>
			<input type="hidden" name="itwt_action" value="save_settings" />

			<div
				class="itwt-tab-panel"
				id="itwt-panel-general"
				role="tabpanel"
				aria-labelledby="itwt-tab-btn-general"
				data-tab="general"
				tabindex="0"
			>
				<div class="itwt-settings-panel">
					<p class="itwt-settings-panel__hint"><?php esc_html_e( 'ข้อมูลติดต่อ โลโก้ และหน้าที่ใช้ใน QR / หน้าเช็คประกัน', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-settings-grid">
						<label for="company_name_th"><?php esc_html_e( 'ชื่อบริษัท (ไทย) บนสติ๊กเกอร์', 'it-warranty-tracker' ); ?></label>
						<div><input type="text" id="company_name_th" name="company_name_th" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['company_name_th'] ?? '' ) ); ?>" /></div>

						<label for="company_name_en"><?php esc_html_e( 'ชื่อบริษัท (อังกฤษ) บนสติ๊กเกอร์', 'it-warranty-tracker' ); ?></label>
						<div><input type="text" id="company_name_en" name="company_name_en" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['company_name_en'] ?? '' ) ); ?>" /></div>

						<label for="support_phone"><?php esc_html_e( 'เบอร์ศูนย์บริการ', 'it-warranty-tracker' ); ?></label>
						<div><input type="text" id="support_phone" name="support_phone" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['support_phone'] ?? '' ) ); ?>" /></div>

						<span id="itwt-logo-label"><?php esc_html_e( 'โลโก้', 'it-warranty-tracker' ); ?></span>
						<div class="itwt-logo-picker" role="group" aria-labelledby="itwt-logo-label">
							<input type="hidden" id="logo_attachment_id" name="logo_attachment_id" value="<?php echo esc_attr( (string) $logo_id ); ?>" />
							<img id="itwt-logo-preview" class="itwt-logo-picker__preview" <?php echo $logo_url ? 'src="' . esc_url( $logo_url ) . '"' : ''; ?> alt="<?php esc_attr_e( 'ตัวอย่างโลโก้', 'it-warranty-tracker' ); ?>" <?php echo $logo_url ? '' : 'hidden'; ?> />
							<p class="itwt-logo-picker__empty description" <?php echo $logo_url ? 'hidden' : ''; ?>><?php esc_html_e( 'ยังไม่ได้เลือกโลโก้', 'it-warranty-tracker' ); ?></p>
							<div class="itwt-logo-picker__actions">
								<button type="button" class="button" id="itwt-logo-select"><?php esc_html_e( 'เลือกโลโก้', 'it-warranty-tracker' ); ?></button>
								<button type="button" class="button-link button-link-delete" id="itwt-logo-remove" <?php echo $logo_url ? '' : 'hidden'; ?>><?php esc_html_e( 'เอาออก', 'it-warranty-tracker' ); ?></button>
							</div>
						</div>

						<label for="lookup_page_id"><?php esc_html_e( 'หน้าเช็คประกัน', 'it-warranty-tracker' ); ?></label>
						<div>
							<select id="lookup_page_id" name="lookup_page_id">
								<option value="0"><?php esc_html_e( '— เลือกหน้า —', 'it-warranty-tracker' ); ?></option>
								<?php foreach ( $pages as $p ) : ?>
									<option value="<?php echo esc_attr( (string) (int) $p->ID ); ?>" <?php selected( (int) ( $settings['lookup_page_id'] ?? 0 ), (int) $p->ID ); ?>>
										<?php echo esc_html( $p->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'เลือกหน้าแล้วตั้ง Template เป็น «Warranty (IT Warranty Tracker)» — shortcode ไม่บังคับ ใช้ใน QR ห้ามเปลี่ยน URL ภายหลัง', 'it-warranty-tracker' ); ?></p>
						</div>

						<span id="itwt-credit-label"><?php esc_html_e( 'เครดิตผู้พัฒนา', 'it-warranty-tracker' ); ?></span>
						<div class="itwt-field" role="group" aria-labelledby="itwt-credit-label">
							<label class="itwt-check" for="show_developer_credit">
								<input
									type="checkbox"
									id="show_developer_credit"
									name="show_developer_credit"
									value="1"
									<?php checked( ! empty( $settings['show_developer_credit'] ) ); ?>
								/>
								<?php esc_html_e( 'แสดงเครดิตผู้พัฒนา (ต้นกล้า ไอที) บนแอดมินและหน้าเช็คประกัน', 'it-warranty-tracker' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'ค่าเริ่มต้นเปิด — ปิดได้หากไม่ต้องการ backlink (ไม่แสดงบนสติ๊กเกอร์พิมพ์)', 'it-warranty-tracker' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div
				class="itwt-tab-panel"
				id="itwt-panel-types"
				role="tabpanel"
				aria-labelledby="itwt-tab-btn-types"
				data-tab="types"
				tabindex="0"
				hidden
			>
				<div class="itwt-settings-panel">
					<p class="itwt-settings-panel__hint"><?php esc_html_e( 'ใช้ในฟอร์มทรัพย์สินและตัวกรอง — slug เป็นภาษาอังกฤษตัวเล็ก (สูงสุด 30 ตัวอักษร) ชื่อที่แสดงแก้ได้', 'it-warranty-tracker' ); ?></p>
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
				</div>
			</div>

			<div
				class="itwt-tab-panel"
				id="itwt-panel-portal"
				role="tabpanel"
				aria-labelledby="itwt-tab-btn-portal"
				data-tab="portal"
				tabindex="0"
				hidden
			>
				<div class="itwt-settings-panel">
					<p class="itwt-settings-panel__hint"><?php esc_html_e( 'ธีม หัวข้อ และข้อมูลที่ลูกค้าเห็นบนหน้าเช็คประกัน', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-settings-grid">
						<label for="portal_title"><?php esc_html_e( 'หัวข้อหน้า', 'it-warranty-tracker' ); ?></label>
						<div><input type="text" id="portal_title" name="portal_title" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['portal_title'] ?? '' ) ); ?>" /></div>

						<label for="portal_description"><?php esc_html_e( 'คำอธิบายใต้หัวข้อ', 'it-warranty-tracker' ); ?></label>
						<div><textarea id="portal_description" name="portal_description" class="large-text" rows="2"><?php echo esc_textarea( (string) ( $settings['portal_description'] ?? '' ) ); ?></textarea></div>

						<label for="portal_theme"><?php esc_html_e( 'ธีมหน้าเช็คประกัน', 'it-warranty-tracker' ); ?></label>
						<div>
							<select id="portal_theme" name="portal_theme" aria-describedby="portal_theme_desc">
								<option value="soft" <?php selected( $portal_theme, 'soft' ); ?>><?php esc_html_e( 'Soft — การ์ดนุ่ม มีเงา', 'it-warranty-tracker' ); ?></option>
								<option value="minimal" <?php selected( $portal_theme, 'minimal' ); ?>><?php esc_html_e( 'Minimal — เส้นบาง โล่ง ไม่มีเงา', 'it-warranty-tracker' ); ?></option>
								<option value="dense" <?php selected( $portal_theme, 'dense' ); ?>><?php esc_html_e( 'Dense — แน่น ตัวอักษรเล็ก อ่านข้อมูลเยอะ', 'it-warranty-tracker' ); ?></option>
							</select>
							<p class="description" id="portal_theme_desc"><?php esc_html_e( 'เปลี่ยนแล้วกดบันทึก — ลองสลับ Minimal / Dense จะเห็นความต่างชัดกว่า Soft (ค่าเริ่มต้น)', 'it-warranty-tracker' ); ?></p>
						</div>

						<label for="portal_accent_color"><?php esc_html_e( 'สีหลัก', 'it-warranty-tracker' ); ?></label>
						<div>
							<input type="text" id="portal_accent_color" name="portal_accent_color" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['portal_accent_color'] ?? '' ) ); ?>" placeholder="#2271b1" pattern="#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?" maxlength="7" aria-describedby="portal_accent_color_desc" />
							<p class="description" id="portal_accent_color_desc"><?php esc_html_e( 'รหัสสี HEX เช่น #2271b1 — ว่างไว้เพื่อใช้สีเริ่มต้น', 'it-warranty-tracker' ); ?></p>
						</div>

						<label for="privacy_policy_url"><?php esc_html_e( 'ลิงก์นโยบายความเป็นส่วนตัว', 'it-warranty-tracker' ); ?></label>
						<div><input type="url" id="privacy_policy_url" name="privacy_policy_url" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['privacy_policy_url'] ?? '' ) ); ?>" placeholder="https://" /></div>

						<span id="itwt-display-label"><?php esc_html_e( 'ข้อมูลที่แสดง', 'it-warranty-tracker' ); ?></span>
						<fieldset aria-labelledby="itwt-display-label">
							<?php foreach ( $display_toggles as $key => $label ) : ?>
								<label class="itwt-check">
									<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?> />
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>
			</div>

			<div
				class="itwt-tab-panel"
				id="itwt-panel-alerts"
				role="tabpanel"
				aria-labelledby="itwt-tab-btn-alerts"
				data-tab="alerts"
				tabindex="0"
				hidden
			>
				<div class="itwt-settings-panel">
					<p class="itwt-settings-panel__hint"><?php esc_html_e( 'เกณฑ์ใกล้หมดประกัน รอบแจ้งเตือน และระยะเก็บคำขอ', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-settings-grid">
						<label for="expiring_days"><?php esc_html_e( 'วันใกล้หมด (expiring)', 'it-warranty-tracker' ); ?></label>
						<div><input type="number" id="expiring_days" name="expiring_days" value="<?php echo esc_attr( (string) (int) ( $settings['expiring_days'] ?? 30 ) ); ?>" min="1" max="365" /></div>

						<label for="milestones"><?php esc_html_e( 'Milestones แจ้งเตือน (วัน)', 'it-warranty-tracker' ); ?></label>
						<div>
							<input type="text" id="milestones" name="milestones" class="regular-text" value="<?php echo esc_attr( $milestones ); ?>" aria-describedby="milestones_desc" />
							<p class="description" id="milestones_desc"><?php esc_html_e( 'คั่นด้วยจุลภาค เช่น 60,30,7,0', 'it-warranty-tracker' ); ?></p>
						</div>

						<label for="request_retention_months"><?php esc_html_e( 'เก็บคำขอ (เดือน)', 'it-warranty-tracker' ); ?></label>
						<div><input type="number" id="request_retention_months" name="request_retention_months" value="<?php echo esc_attr( (string) (int) ( $settings['request_retention_months'] ?? 24 ) ); ?>" min="1" max="120" /></div>
					</div>
				</div>
			</div>

			<div
				class="itwt-tab-panel"
				id="itwt-panel-line"
				role="tabpanel"
				aria-labelledby="itwt-tab-btn-line"
				data-tab="line"
				tabindex="0"
				hidden
			>
				<div class="itwt-settings-panel">
					<p class="itwt-settings-panel__hint"><?php esc_html_e( 'LINE OA โควตา กลุ่มช่าง และข้อความยินยอม', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-settings-grid">
						<label for="line_oa_id"><?php esc_html_e( 'LINE OA ID', 'it-warranty-tracker' ); ?></label>
						<div><input type="text" id="line_oa_id" name="line_oa_id" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['line_oa_id'] ?? '' ) ); ?>" placeholder="@xxxx" /></div>

						<label for="line_monthly_quota"><?php esc_html_e( 'โควตา LINE / เดือน', 'it-warranty-tracker' ); ?></label>
						<div><input type="number" id="line_monthly_quota" name="line_monthly_quota" value="<?php echo esc_attr( (string) (int) ( $settings['line_monthly_quota'] ?? 300 ) ); ?>" min="1" /></div>

						<label for="line_customer_reserve"><?php esc_html_e( 'สงวนโควตาให้ลูกค้า', 'it-warranty-tracker' ); ?></label>
						<div><input type="number" id="line_customer_reserve" name="line_customer_reserve" value="<?php echo esc_attr( (string) (int) ( $settings['line_customer_reserve'] ?? 150 ) ); ?>" min="0" /></div>

						<span><?php esc_html_e( 'กลุ่มช่าง LINE', 'it-warranty-tracker' ); ?></span>
						<div>
							<code><?php echo esc_html( '' !== (string) ( $settings['line_tech_group_id'] ?? '' ) ? (string) $settings['line_tech_group_id'] : '—' ); ?></code>
							<?php if ( ! empty( $settings['line_pending_group_id'] ) ) : ?>
								<p>
									<?php esc_html_e( 'พบกลุ่มรอยืนยัน:', 'it-warranty-tracker' ); ?>
									<code><?php echo esc_html( (string) $settings['line_pending_group_id'] ); ?></code>
								</p>
							<?php endif; ?>
							<p class="description"><?php esc_html_e( 'Secret/Token ใส่ใน wp-config.php เป็น ITWT_LINE_CHANNEL_SECRET และ ITWT_LINE_CHANNEL_TOKEN', 'it-warranty-tracker' ); ?></p>
						</div>

						<span><?php esc_html_e( 'Webhook URL', 'it-warranty-tracker' ); ?></span>
						<div><code><?php echo esc_html( rest_url( 'itwt/v1/line/webhook' ) ); ?></code></div>

						<label for="pdpa_consent_text"><?php esc_html_e( 'ข้อความยินยอม (ฟอร์ม)', 'it-warranty-tracker' ); ?></label>
						<div><textarea id="pdpa_consent_text" name="pdpa_consent_text" class="large-text" rows="3"><?php echo esc_textarea( (string) ( $settings['pdpa_consent_text'] ?? '' ) ); ?></textarea></div>

						<label for="line_pdpa_text"><?php esc_html_e( 'ข้อความ PDPA (LINE)', 'it-warranty-tracker' ); ?></label>
						<div><textarea id="line_pdpa_text" name="line_pdpa_text" class="large-text" rows="3"><?php echo esc_textarea( (string) ( $settings['line_pdpa_text'] ?? '' ) ); ?></textarea></div>
					</div>
				</div>
			</div>

			<footer class="itwt-settings-footer">
				<button type="submit" class="itwt-action itwt-action--primary"><?php esc_html_e( 'บันทึกการตั้งค่า', 'it-warranty-tracker' ); ?></button>
			</footer>
		</form>
	</section>

	<?php if ( ! empty( $settings['line_pending_group_id'] ) ) : ?>
		<form method="post" class="itwt-settings-card itwt-settings-card--aside">
			<?php wp_nonce_field( 'itwt_confirm_tech_group' ); ?>
			<input type="hidden" name="itwt_action" value="confirm_tech_group" />
			<h2><?php esc_html_e( 'ยืนยันกลุ่มช่าง', 'it-warranty-tracker' ); ?></h2>
			<div class="itwt-settings-panel">
				<p>
					<?php esc_html_e( 'กลุ่มรอยืนยัน:', 'it-warranty-tracker' ); ?>
					<code><?php echo esc_html( (string) $settings['line_pending_group_id'] ); ?></code>
				</p>
				<p><?php submit_button( __( 'ยืนยันกลุ่มนี้เป็นกลุ่มช่าง', 'it-warranty-tracker' ), 'secondary', 'submit', false ); ?></p>
			</div>
		</form>
	<?php endif; ?>
</div>
