<?php
/**
 * Asset create/edit form.
 *
 * @package ITWT
 * @var object|null $asset
 * @var array       $contracts
 * @var array       $logs
 * @var array       $customers
 * @var array       $templates
 */

defined( 'ABSPATH' ) || exit;

$is_new   = ! $asset;
$specs    = array();
if ( $asset && ! empty( $asset->specs_json ) ) {
	$decoded = json_decode( (string) $asset->specs_json, true );
	$specs   = is_array( $decoded ) ? $decoded : array();
}
?>
<div class="wrap itwt-wrap">
	<h1><?php echo $is_new ? esc_html__( 'เพิ่มทรัพย์สิน', 'it-warranty-tracker' ) : esc_html__( 'แก้ไขทรัพย์สิน', 'it-warranty-tracker' ); ?></h1>

	<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'บันทึกแล้ว', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>
	<?php if ( ! empty( $_GET['error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error"><p><?php echo esc_html( sanitize_text_field( wp_unslash( (string) $_GET['error'] ) ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" class="itwt-asset-form">
		<?php wp_nonce_field( 'itwt_save_asset' ); ?>
		<input type="hidden" name="itwt_action" value="save_asset" />
		<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) ( $asset->id ?? 0 ) ); ?>" />

		<table class="form-table" role="presentation">
			<tr>
				<th><label for="service_tag"><?php esc_html_e( 'Service Tag', 'it-warranty-tracker' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="service_tag" name="service_tag" value="<?php echo esc_attr( (string) ( $asset->service_tag ?? '' ) ); ?>" maxlength="20" <?php echo $is_new ? '' : 'readonly'; ?> />
					<?php if ( $is_new ) : ?>
						<button type="button" class="button" id="itwt-gen-tag"><?php esc_html_e( 'สุ่มแท็ก', 'it-warranty-tracker' ); ?></button>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><label for="brand"><?php esc_html_e( 'ยี่ห้อ', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" class="regular-text" id="brand" name="brand" value="<?php echo esc_attr( (string) ( $asset->brand ?? '' ) ); ?>" required /></td>
			</tr>
			<tr>
				<th><label for="serial_number"><?php esc_html_e( 'Serial Number', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" class="regular-text" id="serial_number" name="serial_number" value="<?php echo esc_attr( (string) ( $asset->serial_number ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="asset_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label></th>
				<td>
					<?php
					$asset_types   = \ITWT\Asset_Types::all();
					$current_type  = (string) ( $asset->asset_type ?? \ITWT\Asset_Types::default_slug() );
					?>
					<select id="asset_type" name="asset_type" data-itwt-spec-type>
						<?php foreach ( $asset_types as $type ) : ?>
							<option value="<?php echo esc_attr( $type['slug'] ); ?>" <?php selected( $current_type, $type['slug'] ); ?>><?php echo esc_html( $type['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="model_name"><?php esc_html_e( 'รุ่น / ชื่อโปรเจกต์', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" class="regular-text" id="model_name" name="model_name" value="<?php echo esc_attr( (string) ( $asset->model_name ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="customer_id"><?php esc_html_e( 'ลูกค้า', 'it-warranty-tracker' ); ?></label></th>
				<td>
					<select id="customer_id" name="customer_id">
						<option value="0"><?php esc_html_e( '— เลือก —', 'it-warranty-tracker' ); ?></option>
						<?php foreach ( $customers['items'] as $c ) : ?>
							<option value="<?php echo esc_attr( (string) (int) $c->id ); ?>" <?php selected( (int) ( $asset->customer_id ?? 0 ), (int) $c->id ); ?>>
								<?php echo esc_html( $c->customer_code . ' — ' . $c->company_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="install_location"><?php esc_html_e( 'สถานที่ติดตั้ง (ภายใน)', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" class="regular-text" id="install_location" name="install_location" value="<?php echo esc_attr( (string) ( $asset->install_location ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="status"><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></label></th>
				<td>
					<select id="status" name="status">
						<?php foreach ( array( 'active', 'void', 'decommissioned' ) as $st ) : ?>
							<option value="<?php echo esc_attr( $st ); ?>" <?php selected( (string) ( $asset->status ?? 'active' ), $st ); ?>><?php echo esc_html( $st ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="internal_note"><?php esc_html_e( 'หมายเหตุภายใน', 'it-warranty-tracker' ); ?></label></th>
				<td><textarea id="internal_note" name="internal_note" rows="3" class="large-text"><?php echo esc_textarea( (string) ( $asset->internal_note ?? '' ) ); ?></textarea></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'สเปก', 'it-warranty-tracker' ); ?></h2>
		<div id="itwt-spec-builder" class="itwt-spec-builder">
			<?php
			if ( empty( $specs ) ) {
				$specs = array( array( 'key' => '', 'value' => '', 'public' => true ) );
			}
			foreach ( $specs as $i => $spec ) :
				?>
				<div class="itwt-spec-row">
					<input type="text" name="spec_key[<?php echo esc_attr( (string) $i ); ?>]" placeholder="<?php esc_attr_e( 'คีย์', 'it-warranty-tracker' ); ?>" value="<?php echo esc_attr( (string) ( $spec['key'] ?? '' ) ); ?>" />
					<input type="text" name="spec_value[<?php echo esc_attr( (string) $i ); ?>]" placeholder="<?php esc_attr_e( 'ค่า', 'it-warranty-tracker' ); ?>" value="<?php echo esc_attr( (string) ( $spec['value'] ?? '' ) ); ?>" />
					<label>
						<input type="checkbox" name="spec_public[<?php echo esc_attr( (string) $i ); ?>]" value="1" <?php checked( ! empty( $spec['public'] ) ); ?> />
						<?php esc_html_e( 'แสดงหน้าเว็บ', 'it-warranty-tracker' ); ?>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="itwt-add-spec"><?php esc_html_e( 'เพิ่มสเปก', 'it-warranty-tracker' ); ?></button>
		<button type="button" class="button" id="itwt-load-template"><?php esc_html_e( 'โหลดเทมเพลต', 'it-warranty-tracker' ); ?></button>

		<?php if ( $is_new ) : ?>
			<h2><?php esc_html_e( 'สัญญาแรก (ถ้ามี)', 'it-warranty-tracker' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></th>
					<td>
						<select name="new_contract_type">
							<option value=""><?php esc_html_e( '— ไม่เพิ่ม —', 'it-warranty-tracker' ); ?></option>
							<option value="manufacturer">manufacturer</option>
							<option value="warranty">warranty</option>
							<option value="ma">ma</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'เลขที่สัญญา', 'it-warranty-tracker' ); ?></th>
					<td><input type="text" name="new_contract_no" class="regular-text" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'วันเริ่ม / สิ้นสุด', 'it-warranty-tracker' ); ?></th>
					<td>
						<input type="date" name="new_start_date" /> —
						<input type="date" name="new_end_date" id="new_end_date" />
						<button type="button" class="button itwt-years" data-years="1">+1 ปี</button>
						<button type="button" class="button itwt-years" data-years="2">+2 ปี</button>
						<button type="button" class="button itwt-years" data-years="3">+3 ปี</button>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?></th>
					<td><input type="text" name="new_sla_tier" class="regular-text" placeholder="On-Site 24x7 / NBD / Carry-in" /></td>
				</tr>
			</table>
		<?php endif; ?>

		<?php submit_button( __( 'บันทึกทรัพย์สิน', 'it-warranty-tracker' ) ); ?>
	</form>

	<?php if ( ! $is_new && $asset ) : ?>
		<hr />
		<h2><?php esc_html_e( 'สัญญา / ต่อ MA', 'it-warranty-tracker' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'เลขที่', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'ช่วงวันที่', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $contracts ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'ยังไม่มีสัญญา', 'it-warranty-tracker' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $contracts as $c ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $c->contract_type ); ?></td>
							<td><?php echo esc_html( (string) $c->contract_no ); ?></td>
							<td><?php echo esc_html( $c->start_date . ' → ' . $c->end_date ); ?></td>
							<td><?php echo esc_html( (string) $c->sla_tier ); ?></td>
							<td><?php echo esc_html( (string) $c->status ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<form method="post" class="itwt-renew-form">
			<?php wp_nonce_field( 'itwt_save_contract' ); ?>
			<input type="hidden" name="itwt_action" value="save_contract" />
			<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) (int) $asset->id ); ?>" />
			<input type="hidden" name="contract_type" value="ma" />
			<h3><?php esc_html_e( 'ต่อ MA', 'it-warranty-tracker' ); ?></h3>
			<p>
				<label><?php esc_html_e( 'เลขที่', 'it-warranty-tracker' ); ?> <input type="text" name="contract_no" /></label>
				<label><?php esc_html_e( 'เริ่ม', 'it-warranty-tracker' ); ?> <input type="date" name="start_date" id="ma_start" required /></label>
				<label><?php esc_html_e( 'สิ้นสุด', 'it-warranty-tracker' ); ?> <input type="date" name="end_date" id="ma_end" required /></label>
				<button type="button" class="button itwt-ma-years" data-years="1">1 ปี</button>
				<button type="button" class="button itwt-ma-years" data-years="2">2 ปี</button>
				<button type="button" class="button itwt-ma-years" data-years="3">3 ปี</button>
				<label><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?> <input type="text" name="sla_tier" /></label>
				<?php submit_button( __( 'เพิ่มสัญญา MA', 'it-warranty-tracker' ), 'primary', 'submit', false ); ?>
			</p>
		</form>

		<hr />
		<h2><?php esc_html_e( 'ประวัติบริการ', 'it-warranty-tracker' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'วันที่', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></th>
					<th><?php esc_html_e( 'สรุปสาธารณะ', 'it-warranty-tracker' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="3"><?php esc_html_e( 'ยังไม่มีบันทึก', 'it-warranty-tracker' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $log->service_date ); ?></td>
							<td><?php echo esc_html( (string) $log->service_type ); ?></td>
							<td><?php echo esc_html( (string) ( $log->public_summary ?? '—' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<form method="post">
			<?php wp_nonce_field( 'itwt_save_service_log' ); ?>
			<input type="hidden" name="itwt_action" value="save_service_log" />
			<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) (int) $asset->id ); ?>" />
			<input type="hidden" name="log_id" value="0" />
			<h3><?php esc_html_e( 'เพิ่มบันทึกบริการ', 'it-warranty-tracker' ); ?></h3>
			<p>
				<label><?php esc_html_e( 'วันที่', 'it-warranty-tracker' ); ?> <input type="date" name="service_date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required /></label>
				<label><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?>
					<select name="service_type">
						<?php foreach ( array( 'claim', 'pm', 'upgrade', 'incident' ) as $st ) : ?>
							<option value="<?php echo esc_attr( $st ); ?>"><?php echo esc_html( $st ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
			<p><label><?php esc_html_e( 'อาการ', 'it-warranty-tracker' ); ?><br /><textarea name="symptom" class="large-text" rows="2"></textarea></label></p>
			<p><label><?php esc_html_e( 'การแก้ไข', 'it-warranty-tracker' ); ?><br /><textarea name="resolution" class="large-text" rows="2"></textarea></label></p>
			<p><label><?php esc_html_e( 'สรุปสาธารณะ (ว่าง = ไม่แสดง)', 'it-warranty-tracker' ); ?><br /><input type="text" name="public_summary" class="large-text" maxlength="500" /></label></p>
			<?php submit_button( __( 'บันทึกบริการ', 'it-warranty-tracker' ), 'secondary' ); ?>
		</form>

		<?php if ( current_user_can( 'itwt_manage_assets' ) ) : ?>
			<form method="post" onsubmit="return confirm(itwtAdmin.i18n.confirmDelete);">
				<?php wp_nonce_field( 'itwt_delete_asset' ); ?>
				<input type="hidden" name="itwt_action" value="delete_asset" />
				<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) (int) $asset->id ); ?>" />
				<?php submit_button( __( 'ลบ / เลิกใช้งาน', 'it-warranty-tracker' ), 'delete' ); ?>
			</form>
		<?php endif; ?>
	<?php endif; ?>
</div>
