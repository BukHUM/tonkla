<?php
/**
 * Asset create/edit form (tabbed).
 *
 * @package ITWT
 * @var object|null $asset
 * @var array       $contracts
 * @var array       $logs
 * @var array       $components
 * @var array       $templates
 * @var int         $selected_customer_id
 * @var object|null $selected_customer
 * @var int         $ma_count
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Coverage;
use ITWT\Dates;
use ITWT\Labels;
use ITWT\Spec_Icons;

$is_new     = ! $asset;
$can_manage = current_user_can( 'itwt_manage_assets' );
$can_log    = $can_manage || current_user_can( 'itwt_log_service' );
$asset_id   = (int) ( $asset->id ?? 0 );
$specs      = array();
if ( $asset && ! empty( $asset->specs_json ) ) {
	$decoded = json_decode( (string) $asset->specs_json, true );
	$specs   = is_array( $decoded ) ? $decoded : array();
}
if ( empty( $specs ) ) {
	$specs = array(
		array(
			'key'    => '',
			'value'  => '',
			'public' => true,
		),
	);
}

$components = isset( $components ) && is_array( $components ) ? $components : array();
$component_count = 0;
foreach ( $components as $comp_row ) {
	$name = is_object( $comp_row ) ? (string) ( $comp_row->item_name ?? '' ) : (string) ( $comp_row['item_name'] ?? '' );
	if ( '' !== $name ) {
		++$component_count;
	}
}
if ( empty( $components ) ) {
	$components = array(
		(object) array(
			'item_name'    => '',
			'source'       => '',
			'warranty_end' => '',
			'note'          => '',
		),
	);
}
$ma_count = isset( $ma_count ) ? (int) $ma_count : 0;

$coverage             = null;
$selected_customer_id = isset( $selected_customer_id ) ? (int) $selected_customer_id : (int) ( $asset->customer_id ?? 0 );
$selected_customer    = $selected_customer ?? null;
$customer_name        = $selected_customer ? (string) $selected_customer->company_name : '';
$customer_label       = '';
if ( $selected_customer ) {
	$customer_label = (string) $selected_customer->customer_code . ' — ' . (string) $selected_customer->company_name;
}
if ( $asset ) {
	$coverage = Coverage::compute( (string) $asset->status, $contracts );
}
$customer_return_url = $is_new
	? admin_url( 'admin.php?page=itwt-assets&action=new' )
	: admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . $asset_id );

$tabs = array(
	'overview'   => __( 'ข้อมูลหลัก', 'it-warranty-tracker' ),
	'specs'      => __( 'สเปก', 'it-warranty-tracker' ),
	'components' => __( 'อุปกรณ์', 'it-warranty-tracker' ),
	'contracts'  => __( 'สัญญา / MA', 'it-warranty-tracker' ),
);
if ( ! $is_new ) {
	$tabs['service'] = __( 'ประวัติบริการ', 'it-warranty-tracker' );
	$tabs['tools']   = __( 'เครื่องมือ', 'it-warranty-tracker' );
}
$first_tab = 'overview';
$readonly  = ! $can_manage;

/**
 * Print opening markup of a tab panel.
 *
 * @param string $slug      Tab slug.
 * @param string $first_tab Initially visible tab.
 */
$itwt_panel_open = static function ( string $slug, string $first_tab ): void {
	printf(
		'<div class="itwt-tab-panel" id="itwt-panel-%1$s" role="tabpanel" aria-labelledby="itwt-tab-btn-%1$s" data-tab="%1$s" tabindex="0"%2$s>',
		esc_attr( $slug ),
		$slug === $first_tab ? '' : ' hidden'
	);
};

$model_line = $asset
	? trim( (string) $asset->brand . ' ' . (string) ( $asset->model_name ?? '' ) )
	: '';
?>
<div class="wrap itwt-wrap">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php echo $is_new ? esc_html__( 'เพิ่มทรัพย์สิน', 'it-warranty-tracker' ) : esc_html__( 'แก้ไขทรัพย์สิน', 'it-warranty-tracker' ); ?></h1>
		</div>
		<div class="itwt-page-header__actions">
			<a class="itwt-action" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets' ) ); ?>"><?php esc_html_e( 'กลับรายการ', 'it-warranty-tracker' ); ?></a>
			<?php if ( $can_manage ) : ?>
				<button type="submit" form="itwt-asset-form" class="itwt-action itwt-action--primary"><?php esc_html_e( 'บันทึกทรัพย์สิน', 'it-warranty-tracker' ); ?></button>
			<?php endif; ?>
		</div>
	</header>

	<?php
	if ( ! empty( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'บันทึกแล้ว', 'it-warranty-tracker' ) );
	}
	if ( ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error_raw = wp_unslash( (string) $_GET['error'] );
		if ( '1' === $error_raw ) {
			\ITWT\Toast::push( 'error', __( 'ดำเนินการไม่สำเร็จ', 'it-warranty-tracker' ) );
		} else {
			\ITWT\Toast::push( 'error', sanitize_text_field( $error_raw ) );
		}
	}
	if ( ! empty( $_GET['customer_saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		\ITWT\Toast::push( 'success', __( 'บันทึกรายชื่อแล้ว — เลือกให้อัตโนมัติ', 'it-warranty-tracker' ) );
	}
	?>

	<?php if ( ! $is_new && $coverage ) : ?>
		<section class="itwt-summary" aria-label="<?php esc_attr_e( 'สรุปทรัพย์สิน', 'it-warranty-tracker' ); ?>">
			<div class="itwt-summary__identity">
				<code class="itwt-summary__tag"><?php echo esc_html( (string) $asset->service_tag ); ?></code>
				<span class="itwt-badge itwt-badge--<?php echo esc_attr( $coverage['status'] ); ?>">
					<?php
					echo esc_html(
						Coverage::label(
							$coverage['status'],
							$coverage['days_remaining'],
							! empty( $coverage['next_start'] ) ? (string) $coverage['next_start'] : null
						)
					);
					?>
				</span>
			</div>
			<div class="itwt-summary__meta">
				<?php if ( '' !== $customer_name ) : ?>
					<span class="itwt-summary__chip">
						<span class="itwt-summary__chip-label"><?php esc_html_e( 'ลูกค้า', 'it-warranty-tracker' ); ?></span>
						<span class="itwt-summary__chip-value"><?php echo esc_html( $customer_name ); ?></span>
					</span>
				<?php endif; ?>
				<?php if ( '' !== $model_line ) : ?>
					<span class="itwt-summary__chip">
						<span class="itwt-summary__chip-label"><?php esc_html_e( 'รุ่น', 'it-warranty-tracker' ); ?></span>
						<span class="itwt-summary__chip-value"><?php echo esc_html( $model_line ); ?></span>
					</span>
				<?php endif; ?>
				<?php
				$summary_end = ! empty( $asset->warranty_end_date )
					? (string) $asset->warranty_end_date
					: (string) ( $coverage['latest_end'] ?? '' );
				?>
				<?php if ( '' !== $summary_end ) : ?>
					<span class="itwt-summary__chip">
						<span class="itwt-summary__chip-label"><?php esc_html_e( 'สิ้นสุด', 'it-warranty-tracker' ); ?></span>
						<span class="itwt-summary__chip-value"><?php echo esc_html( Dates::format( $summary_end ) ); ?></span>
					</span>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="itwt-panel itwt-asset-shell">
		<div class="itwt-tabs" role="tablist" aria-label="<?php esc_attr_e( 'ส่วนของทรัพย์สิน', 'it-warranty-tracker' ); ?>">
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
				>
					<?php echo esc_html( $label ); ?>
					<?php if ( 'components' === $slug && $component_count > 0 ) : ?>
						<span class="itwt-tab__count"><?php echo esc_html( (string) $component_count ); ?></span>
					<?php elseif ( 'contracts' === $slug && ! $is_new ) : ?>
						<span class="itwt-tab__count"><?php echo esc_html( (string) count( $contracts ) ); ?></span>
					<?php elseif ( 'service' === $slug ) : ?>
						<span class="itwt-tab__count"><?php echo esc_html( (string) count( $logs ) ); ?></span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>

		<form method="post" id="itwt-asset-form" class="itwt-asset-form">
			<?php wp_nonce_field( 'itwt_save_asset' ); ?>
			<input type="hidden" name="itwt_action" value="save_asset" />
			<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />

			<?php $itwt_panel_open( 'overview', $first_tab ); ?>
				<?php if ( $readonly ) : ?>
					<p class="itwt-inline-notice"><?php esc_html_e( 'คุณดูข้อมูลได้อย่างเดียว — บันทึกบริการได้ที่แท็บประวัติบริการ', 'it-warranty-tracker' ); ?></p>
				<?php endif; ?>
				<fieldset class="itwt-fieldset" <?php disabled( $readonly ); ?>>
					<div class="itwt-field-grid">
						<label for="service_tag"><?php esc_html_e( 'Service Tag', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field itwt-field--inline">
							<input type="text" id="service_tag" name="service_tag" value="<?php echo esc_attr( (string) ( $asset->service_tag ?? '' ) ); ?>" maxlength="20" <?php echo $is_new ? '' : 'readonly'; ?> autocomplete="off" />
							<?php if ( $is_new ) : ?>
								<button type="button" class="itwt-action" id="itwt-gen-tag"><?php esc_html_e( 'สุ่มแท็ก', 'it-warranty-tracker' ); ?></button>
							<?php endif; ?>
						</div>

						<label for="brand"><?php esc_html_e( 'ยี่ห้อ', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" id="brand" name="brand" value="<?php echo esc_attr( (string) ( $asset->brand ?? '' ) ); ?>" required />
						</div>

						<label for="serial_number"><?php esc_html_e( 'Serial Number', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" id="serial_number" name="serial_number" value="<?php echo esc_attr( (string) ( $asset->serial_number ?? '' ) ); ?>" />
						</div>

						<label for="asset_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<?php $current_type = (string) ( $asset->asset_type ?? \ITWT\Asset_Types::default_slug() ); ?>
							<select id="asset_type" name="asset_type" data-itwt-spec-type>
								<?php foreach ( \ITWT\Asset_Types::all() as $type ) : ?>
									<option value="<?php echo esc_attr( $type['slug'] ); ?>" <?php selected( $current_type, $type['slug'] ); ?>><?php echo esc_html( $type['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<label for="model_name"><?php esc_html_e( 'รุ่น / ชื่อโปรเจกต์', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" id="model_name" name="model_name" value="<?php echo esc_attr( (string) ( $asset->model_name ?? '' ) ); ?>" />
						</div>

						<label for="itwt-customer-search"><?php esc_html_e( 'ลูกค้า', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field itwt-customer-picker" data-itwt-customer-picker>
							<input type="hidden" name="customer_id" id="customer_id" value="<?php echo esc_attr( (string) $selected_customer_id ); ?>" />
							<div class="itwt-customer-picker__row">
								<div class="itwt-combobox">
									<input
										type="search"
										id="itwt-customer-search"
										class="itwt-combobox__input"
										value="<?php echo esc_attr( $customer_label ); ?>"
										placeholder="<?php esc_attr_e( 'ค้นหารหัสหรือชื่อลูกค้า…', 'it-warranty-tracker' ); ?>"
										autocomplete="off"
										aria-autocomplete="list"
										aria-controls="itwt-customer-results"
										aria-expanded="false"
										<?php disabled( $readonly ); ?>
									/>
									<ul id="itwt-customer-results" class="itwt-combobox__list" role="listbox" hidden></ul>
								</div>
								<?php if ( $can_manage ) : ?>
									<button type="button" class="itwt-action itwt-action--add" data-itwt-modal-open="itwt-customer-modal" data-mode="create">
										<?php esc_html_e( 'เพิ่มลูกค้า', 'it-warranty-tracker' ); ?>
									</button>
								<?php endif; ?>
							</div>
							<div
								class="itwt-customer-picker__selected<?php echo $selected_customer_id > 0 ? ' is-visible' : ''; ?>"
								data-itwt-customer-selected
								<?php echo $selected_customer_id > 0 ? '' : ' hidden'; ?>
							>
								<span class="itwt-customer-picker__badge"><?php esc_html_e( 'เลือกแล้ว', 'it-warranty-tracker' ); ?></span>
								<div class="itwt-customer-picker__info">
									<code class="itwt-customer-picker__code" data-itwt-customer-code><?php echo $selected_customer ? esc_html( (string) $selected_customer->customer_code ) : ''; ?></code>
									<span class="itwt-customer-picker__name" data-itwt-customer-name><?php echo $selected_customer ? esc_html( (string) $selected_customer->company_name ) : ''; ?></span>
								</div>
								<?php if ( $can_manage ) : ?>
									<button type="button" class="itwt-customer-picker__clear" data-itwt-customer-clear>
										<?php esc_html_e( 'ล้าง', 'it-warranty-tracker' ); ?>
									</button>
								<?php endif; ?>
							</div>
						</div>

						<label for="install_location"><?php esc_html_e( 'สถานที่ติดตั้ง (ภายใน)', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" id="install_location" name="install_location" value="<?php echo esc_attr( (string) ( $asset->install_location ?? '' ) ); ?>" />
						</div>

						<label for="status"><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<select id="status" name="status">
								<?php foreach ( Labels::asset_statuses() as $st => $st_label ) : ?>
									<option value="<?php echo esc_attr( $st ); ?>" <?php selected( (string) ( $asset->status ?? 'active' ), $st ); ?>><?php echo esc_html( $st_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<section class="itwt-subpanel" aria-labelledby="itwt-warranty-heading">
						<h3 id="itwt-warranty-heading" class="itwt-subpanel__title"><?php esc_html_e( 'ประกันบริษัท', 'it-warranty-tracker' ); ?></h3>
						<p class="itwt-subpanel__hint"><?php esc_html_e( 'กรอกวันซื้อและวันหมดประกันก่อน — ระบบจะสร้างสัญญา «ประกันบริษัท» ให้อัตโนมัติ (ยังไม่ต้องต่อ MA)', 'it-warranty-tracker' ); ?></p>
						<div class="itwt-field-grid">
							<label for="purchase_date"><?php esc_html_e( 'วันซื้อ', 'it-warranty-tracker' ); ?></label>
							<div class="itwt-field">
								<?php
								Dates::render_input(
									array(
										'id'    => 'purchase_date',
										'name'  => 'purchase_date',
										'value' => (string) ( $asset->purchase_date ?? '' ),
									)
								);
								?>
							</div>

							<label for="warranty_end_date"><?php esc_html_e( 'วันหมดประกัน', 'it-warranty-tracker' ); ?></label>
							<div class="itwt-field itwt-field--inline itwt-field--dates">
								<?php
								Dates::render_input(
									array(
										'id'    => 'warranty_end_date',
										'name'  => 'warranty_end_date',
										'value' => (string) ( $asset->warranty_end_date ?? '' ),
									)
								);
								?>
								<button type="button" class="itwt-action itwt-warranty-years" data-years="1"><?php esc_html_e( '+1 ปี', 'it-warranty-tracker' ); ?></button>
								<button type="button" class="itwt-action itwt-warranty-years" data-years="2"><?php esc_html_e( '+2 ปี', 'it-warranty-tracker' ); ?></button>
								<button type="button" class="itwt-action itwt-warranty-years" data-years="3"><?php esc_html_e( '+3 ปี', 'it-warranty-tracker' ); ?></button>
							</div>
						</div>

						<div class="itwt-renew-caps">
							<p class="itwt-renew-caps__title"><?php esc_html_e( 'เงื่อนไขการต่อ MA', 'it-warranty-tracker' ); ?></p>
							<label class="itwt-renew-caps__row">
								<input type="checkbox" name="renew_max_years_enabled" id="renew_max_years_enabled" value="1" <?php checked( ! empty( $asset->renew_max_years_enabled ) ); ?> data-itwt-renew-toggle="years" />
								<span><?php esc_html_e( 'ต่อรวมแล้วไม่เกิน', 'it-warranty-tracker' ); ?></span>
								<input type="number" name="renew_max_years" id="renew_max_years" class="itwt-renew-caps__num" min="1" max="50" step="1" value="<?php echo esc_attr( (string) ( (int) ( $asset->renew_max_years ?? 5 ) ?: 5 ) ); ?>" <?php disabled( empty( $asset->renew_max_years_enabled ) ); ?> />
								<span><?php esc_html_e( 'ปี นับจากวันซื้อ', 'it-warranty-tracker' ); ?></span>
							</label>
							<label class="itwt-renew-caps__row">
								<input type="checkbox" name="renew_max_times_enabled" id="renew_max_times_enabled" value="1" <?php checked( ! empty( $asset->renew_max_times_enabled ) ); ?> data-itwt-renew-toggle="times" />
								<span><?php esc_html_e( 'ต่อได้ไม่เกิน', 'it-warranty-tracker' ); ?></span>
								<input type="number" name="renew_max_times" id="renew_max_times" class="itwt-renew-caps__num" min="1" max="50" step="1" value="<?php echo esc_attr( (string) ( (int) ( $asset->renew_max_times ?? 5 ) ?: 5 ) ); ?>" <?php disabled( empty( $asset->renew_max_times_enabled ) ); ?> />
								<span><?php esc_html_e( 'ครั้ง', 'it-warranty-tracker' ); ?></span>
							</label>
						</div>
					</section>

					<div class="itwt-field-grid">
						<label for="internal_note"><?php esc_html_e( 'หมายเหตุภายใน', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<textarea id="internal_note" name="internal_note" rows="3"><?php echo esc_textarea( (string) ( $asset->internal_note ?? '' ) ); ?></textarea>
						</div>
					</div>
				</fieldset>
			</div>

			<?php $itwt_panel_open( 'specs', $first_tab ); ?>
				<fieldset class="itwt-fieldset" <?php disabled( $readonly ); ?>>
					<div class="itwt-spec-builder" id="itwt-spec-builder">
						<div class="itwt-spec-builder__head" aria-hidden="true">
							<span><?php esc_html_e( 'คีย์', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'ค่า', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'ไอคอน', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'สาธารณะ', 'it-warranty-tracker' ); ?></span>
							<span></span>
						</div>
						<?php foreach ( $specs as $i => $spec ) : ?>
							<?php $spec_icon = Spec_Icons::sanitize( (string) ( $spec['icon'] ?? '' ) ); ?>
							<div class="itwt-spec-row">
								<input type="text" name="spec_key[<?php echo esc_attr( (string) $i ); ?>]" placeholder="<?php esc_attr_e( 'เช่น CPU', 'it-warranty-tracker' ); ?>" aria-label="<?php esc_attr_e( 'คีย์', 'it-warranty-tracker' ); ?>" value="<?php echo esc_attr( (string) ( $spec['key'] ?? '' ) ); ?>" />
								<input type="text" name="spec_value[<?php echo esc_attr( (string) $i ); ?>]" placeholder="<?php esc_attr_e( 'เช่น 32 Cores', 'it-warranty-tracker' ); ?>" aria-label="<?php esc_attr_e( 'ค่า', 'it-warranty-tracker' ); ?>" value="<?php echo esc_attr( (string) ( $spec['value'] ?? '' ) ); ?>" />
								<select name="spec_icon[<?php echo esc_attr( (string) $i ); ?>]" aria-label="<?php esc_attr_e( 'ไอคอน', 'it-warranty-tracker' ); ?>">
									<?php foreach ( Spec_Icons::options() as $icon_slug => $icon_label ) : ?>
										<option value="<?php echo esc_attr( $icon_slug ); ?>" <?php selected( $spec_icon, $icon_slug ); ?>><?php echo esc_html( $icon_label ); ?></option>
									<?php endforeach; ?>
								</select>
								<label class="itwt-spec-row__public">
									<input type="checkbox" name="spec_public[<?php echo esc_attr( (string) $i ); ?>]" value="1" <?php checked( ! empty( $spec['public'] ) ); ?> />
									<span><?php esc_html_e( 'แสดงหน้าเว็บ', 'it-warranty-tracker' ); ?></span>
								</label>
								<button type="button" class="itwt-spec-row__remove" aria-label="<?php esc_attr_e( 'ลบแถวสเปก', 'it-warranty-tracker' ); ?>">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( $can_manage ) : ?>
						<div class="itwt-tab-toolbar itwt-tab-toolbar--start">
							<button type="button" class="itwt-action" id="itwt-add-spec"><?php esc_html_e( 'เพิ่มสเปก', 'it-warranty-tracker' ); ?></button>
							<button type="button" class="itwt-action" id="itwt-load-template"><?php esc_html_e( 'โหลดเทมเพลต', 'it-warranty-tracker' ); ?></button>
						</div>
					<?php endif; ?>
				</fieldset>
			</div>

			<?php $itwt_panel_open( 'components', $first_tab ); ?>
				<fieldset class="itwt-fieldset" <?php disabled( $readonly ); ?>>
					<p class="itwt-subpanel__hint"><?php esc_html_e( 'บันทึกอุปกรณ์ในชุดและประกันต้นทางแยกตามแหล่งซื้อ (ข้อมูลภายใน — ไม่แสดงหน้าเว็บ)', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-comp-builder" id="itwt-comp-builder">
						<div class="itwt-comp-builder__head" aria-hidden="true">
							<span><?php esc_html_e( 'รายการ', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'ซื้อจาก', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'หมดประกันต้นทาง', 'it-warranty-tracker' ); ?></span>
							<span><?php esc_html_e( 'หมายเหตุ', 'it-warranty-tracker' ); ?></span>
							<span></span>
						</div>
						<?php foreach ( $components as $i => $comp ) : ?>
							<?php
							$comp_name = is_object( $comp ) ? (string) ( $comp->item_name ?? '' ) : (string) ( $comp['item_name'] ?? '' );
							$comp_src  = is_object( $comp ) ? (string) ( $comp->source ?? '' ) : (string) ( $comp['source'] ?? '' );
							$comp_end  = is_object( $comp ) ? (string) ( $comp->warranty_end ?? '' ) : (string) ( $comp['warranty_end'] ?? '' );
							$comp_note = is_object( $comp ) ? (string) ( $comp->note ?? '' ) : (string) ( $comp['note'] ?? '' );
							?>
							<div class="itwt-comp-row">
								<input type="text" name="comp_name[<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $comp_name ); ?>" placeholder="<?php esc_attr_e( 'เช่น CPU / PSU', 'it-warranty-tracker' ); ?>" aria-label="<?php esc_attr_e( 'รายการ', 'it-warranty-tracker' ); ?>" />
								<input type="text" name="comp_source[<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $comp_src ); ?>" placeholder="<?php esc_attr_e( 'ร้าน / ผู้จำหน่าย', 'it-warranty-tracker' ); ?>" aria-label="<?php esc_attr_e( 'ซื้อจาก', 'it-warranty-tracker' ); ?>" />
								<?php
								Dates::render_input(
									array(
										'name'       => 'comp_warranty_end[' . $i . ']',
										'value'      => $comp_end,
										'aria_label' => __( 'หมดประกันต้นทาง', 'it-warranty-tracker' ),
										'class'      => 'itwt-date--compact',
									)
								);
								?>
								<input type="text" name="comp_note[<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $comp_note ); ?>" placeholder="<?php esc_attr_e( 'หมายเหตุ', 'it-warranty-tracker' ); ?>" aria-label="<?php esc_attr_e( 'หมายเหตุ', 'it-warranty-tracker' ); ?>" />
								<button type="button" class="itwt-comp-row__remove" aria-label="<?php esc_attr_e( 'ลบแถว', 'it-warranty-tracker' ); ?>">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( $can_manage ) : ?>
						<div class="itwt-tab-toolbar itwt-tab-toolbar--start">
							<button type="button" class="itwt-action" id="itwt-add-comp"><?php esc_html_e( 'เพิ่มรายการ', 'it-warranty-tracker' ); ?></button>
						</div>
					<?php endif; ?>
				</fieldset>
			</div>

			<?php if ( $is_new ) : ?>
				<?php $itwt_panel_open( 'contracts', $first_tab ); ?>
					<p class="itwt-inline-notice"><?php esc_html_e( 'ใส่สัญญาแรกตอนสร้างได้ — ว่างไว้แล้วเพิ่มทีหลังก็ได้', 'it-warranty-tracker' ); ?></p>
					<div class="itwt-field-grid">
						<label for="new_contract_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<select name="new_contract_type" id="new_contract_type">
								<option value=""><?php esc_html_e( '— ไม่เพิ่ม —', 'it-warranty-tracker' ); ?></option>
								<?php foreach ( Labels::contract_types() as $ct => $ct_label ) : ?>
									<option value="<?php echo esc_attr( $ct ); ?>"><?php echo esc_html( $ct_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<label for="new_contract_no"><?php esc_html_e( 'เลขที่สัญญา', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" name="new_contract_no" id="new_contract_no" />
						</div>

						<label for="new_start_date"><?php esc_html_e( 'วันเริ่ม / สิ้นสุด', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field itwt-field--inline itwt-field--dates">
							<?php
							Dates::render_input(
								array(
									'id'   => 'new_start_date',
									'name' => 'new_start_date',
								)
							);
							?>
							<span class="itwt-field__sep" aria-hidden="true">→</span>
							<?php
							Dates::render_input(
								array(
									'id'         => 'new_end_date',
									'name'       => 'new_end_date',
									'aria_label' => __( 'วันสิ้นสุด', 'it-warranty-tracker' ),
								)
							);
							?>
							<button type="button" class="itwt-action itwt-years" data-years="1"><?php esc_html_e( '+1 ปี', 'it-warranty-tracker' ); ?></button>
							<button type="button" class="itwt-action itwt-years" data-years="2"><?php esc_html_e( '+2 ปี', 'it-warranty-tracker' ); ?></button>
							<button type="button" class="itwt-action itwt-years" data-years="3"><?php esc_html_e( '+3 ปี', 'it-warranty-tracker' ); ?></button>
						</div>

						<label for="new_sla_tier"><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?></label>
						<div class="itwt-field">
							<input type="text" name="new_sla_tier" id="new_sla_tier" placeholder="On-Site 24x7 / NBD / Carry-in" />
						</div>
					</div>
				</div>
			<?php endif; ?>
		</form>

		<?php if ( ! $is_new ) : ?>
			<?php $itwt_panel_open( 'contracts', $first_tab ); ?>
				<div class="itwt-tab-toolbar">
					<?php if ( $can_manage ) : ?>
						<button
							type="button"
							class="itwt-action itwt-action--primary"
							data-itwt-modal-open="itwt-contract-modal"
							data-mode="create"
							data-type="ma"
						>
							<?php echo esc_html( sprintf( /* translators: %s: contract type label */ __( 'เพิ่มสัญญา %s', 'it-warranty-tracker' ), Labels::contract_type( 'ma' ) ) ); ?>
						</button>
					<?php endif; ?>
				</div>
				<?php if ( empty( $contracts ) ) : ?>
					<div class="itwt-empty">
						<p class="itwt-empty__title"><?php esc_html_e( 'ยังไม่มีสัญญาสำหรับทรัพย์สินนี้', 'it-warranty-tracker' ); ?></p>
						<?php if ( $can_manage ) : ?>
							<p class="itwt-empty__hint"><?php esc_html_e( 'กดปุ่มด้านบนเพื่อเพิ่มสัญญา MA', 'it-warranty-tracker' ); ?></p>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="itwt-table-wrap">
						<table class="itwt-table">
							<thead>
								<tr>
									<th scope="col"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></th>
									<th scope="col"><?php esc_html_e( 'เลขที่', 'it-warranty-tracker' ); ?></th>
									<th scope="col"><?php esc_html_e( 'ช่วงวันที่', 'it-warranty-tracker' ); ?></th>
									<th scope="col"><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?></th>
									<?php if ( $can_manage ) : ?>
										<th scope="col" class="itwt-table__actions"><?php esc_html_e( 'จัดการ', 'it-warranty-tracker' ); ?></th>
									<?php endif; ?>
									<th scope="col"><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $contracts as $c ) : ?>
									<?php
									$c_id     = (int) $c->id;
									$c_status = (string) $c->status;
									$c_type   = (string) $c->contract_type;
									?>
									<tr>
										<td><?php echo esc_html( Labels::contract_type( $c_type ) ); ?></td>
										<td><?php echo '' !== (string) $c->contract_no ? '<code>' . esc_html( (string) $c->contract_no ) . '</code>' : '—'; ?></td>
										<td class="itwt-date-range"><?php echo esc_html( Dates::format_range( (string) $c->start_date, (string) $c->end_date ) ); ?></td>
										<td><?php echo esc_html( '' !== (string) $c->sla_tier ? (string) $c->sla_tier : '—' ); ?></td>
										<?php if ( $can_manage ) : ?>
											<td class="itwt-table__actions">
												<div class="itwt-row-actions">
													<button
														type="button"
														class="itwt-row-action"
														data-itwt-modal-open="itwt-contract-modal"
														data-mode="edit"
														data-id="<?php echo esc_attr( (string) $c_id ); ?>"
														data-type="<?php echo esc_attr( $c_type ); ?>"
														data-no="<?php echo esc_attr( (string) $c->contract_no ); ?>"
														data-start="<?php echo esc_attr( (string) $c->start_date ); ?>"
														data-end="<?php echo esc_attr( (string) $c->end_date ); ?>"
														data-sla="<?php echo esc_attr( (string) $c->sla_tier ); ?>"
														data-status="<?php echo esc_attr( $c_status ); ?>"
													><?php esc_html_e( 'แก้ไข', 'it-warranty-tracker' ); ?></button>
													<button
														type="submit"
														form="itwt-delete-contract-<?php echo esc_attr( (string) $c_id ); ?>"
														class="itwt-row-action itwt-row-action--danger"
														data-itwt-confirm="delete"
														data-itwt-confirm-title="<?php esc_attr_e( 'ลบสัญญา?', 'it-warranty-tracker' ); ?>"
														data-itwt-confirm-message="<?php echo esc_attr( sprintf( /* translators: %s: contract type */ __( 'ลบสัญญา «%s» ออกจากทรัพย์สินนี้ — ยกเลิกไม่ได้', 'it-warranty-tracker' ), Labels::contract_type( $c_type ) ) ); ?>"
														data-itwt-confirm-ok="<?php esc_attr_e( 'ลบสัญญา', 'it-warranty-tracker' ); ?>"
													><?php esc_html_e( 'ลบ', 'it-warranty-tracker' ); ?></button>
												</div>
											</td>
										<?php endif; ?>
										<td>
											<span class="itwt-badge-status itwt-badge-status--contract-<?php echo esc_attr( $c_status ); ?>">
												<?php echo esc_html( Labels::contract_status( $c_status ) ); ?>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php if ( $can_manage ) : ?>
						<?php foreach ( $contracts as $c ) : ?>
							<form method="post" id="itwt-delete-contract-<?php echo esc_attr( (string) (int) $c->id ); ?>" class="itwt-hidden-form" hidden>
								<?php wp_nonce_field( 'itwt_delete_contract' ); ?>
								<input type="hidden" name="itwt_action" value="delete_contract" />
								<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />
								<input type="hidden" name="contract_id" value="<?php echo esc_attr( (string) (int) $c->id ); ?>" />
							</form>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php $itwt_panel_open( 'service', $first_tab ); ?>
				<?php if ( $can_log ) : ?>
					<div class="itwt-tab-toolbar">
						<button
							type="button"
							class="itwt-action itwt-action--primary"
							data-itwt-modal-open="itwt-service-modal"
							data-mode="create"
						>
							<?php esc_html_e( 'เพิ่มบันทึกบริการ', 'it-warranty-tracker' ); ?>
						</button>
					</div>
				<?php endif; ?>
				<?php if ( empty( $logs ) ) : ?>
					<div class="itwt-empty">
						<p class="itwt-empty__title"><?php esc_html_e( 'ยังไม่มีบันทึกบริการ', 'it-warranty-tracker' ); ?></p>
						<?php if ( $can_log ) : ?>
							<p class="itwt-empty__hint"><?php esc_html_e( 'กดปุ่มด้านบนเพื่อบันทึกงานเคลม, PM หรืออัปเกรด', 'it-warranty-tracker' ); ?></p>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="itwt-table-wrap">
						<table class="itwt-table">
							<thead>
								<tr>
									<th scope="col"><?php esc_html_e( 'วันที่', 'it-warranty-tracker' ); ?></th>
									<th scope="col"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></th>
									<th scope="col"><?php esc_html_e( 'สรุปสาธารณะ', 'it-warranty-tracker' ); ?></th>
									<?php if ( $can_log ) : ?>
										<th scope="col" class="itwt-table__actions"><?php esc_html_e( 'จัดการ', 'it-warranty-tracker' ); ?></th>
									<?php endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $logs as $log ) : ?>
									<?php $log_id = (int) $log->id; ?>
									<tr>
										<td><?php echo esc_html( Dates::format( (string) $log->service_date ) ); ?></td>
										<td>
											<span class="itwt-badge-status itwt-badge-status--service">
												<?php echo esc_html( Labels::service_type( (string) $log->service_type ) ); ?>
											</span>
										</td>
										<td><?php echo esc_html( '' !== (string) ( $log->public_summary ?? '' ) ? (string) $log->public_summary : '—' ); ?></td>
										<?php if ( $can_log ) : ?>
											<td class="itwt-table__actions">
												<div class="itwt-row-actions">
													<button
														type="button"
														class="itwt-row-action"
														data-itwt-modal-open="itwt-service-modal"
														data-mode="edit"
														data-id="<?php echo esc_attr( (string) $log_id ); ?>"
														data-date="<?php echo esc_attr( (string) $log->service_date ); ?>"
														data-type="<?php echo esc_attr( (string) $log->service_type ); ?>"
														data-symptom="<?php echo esc_attr( (string) ( $log->symptom ?? '' ) ); ?>"
														data-resolution="<?php echo esc_attr( (string) ( $log->resolution ?? '' ) ); ?>"
														data-summary="<?php echo esc_attr( (string) ( $log->public_summary ?? '' ) ); ?>"
													><?php esc_html_e( 'แก้ไข', 'it-warranty-tracker' ); ?></button>
													<button
														type="submit"
														form="itwt-delete-service-<?php echo esc_attr( (string) $log_id ); ?>"
														class="itwt-row-action itwt-row-action--danger"
														data-itwt-confirm="delete"
														data-itwt-confirm-title="<?php esc_attr_e( 'ลบบันทึกบริการ?', 'it-warranty-tracker' ); ?>"
														data-itwt-confirm-message="<?php esc_attr_e( 'ลบบันทึกนี้ออกจากประวัติ — ยกเลิกไม่ได้', 'it-warranty-tracker' ); ?>"
														data-itwt-confirm-ok="<?php esc_attr_e( 'ลบบันทึก', 'it-warranty-tracker' ); ?>"
													><?php esc_html_e( 'ลบ', 'it-warranty-tracker' ); ?></button>
												</div>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php if ( $can_log ) : ?>
						<?php foreach ( $logs as $log ) : ?>
							<form method="post" id="itwt-delete-service-<?php echo esc_attr( (string) (int) $log->id ); ?>" class="itwt-hidden-form" hidden>
								<?php wp_nonce_field( 'itwt_delete_service_log' ); ?>
								<input type="hidden" name="itwt_action" value="delete_service_log" />
								<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />
								<input type="hidden" name="log_id" value="<?php echo esc_attr( (string) (int) $log->id ); ?>" />
							</form>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php $itwt_panel_open( 'tools', $first_tab ); ?>
				<div class="itwt-tool-grid">
					<?php if ( $can_manage ) : ?>
						<a class="itwt-tool-card" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-print-label&ids=' . $asset_id ) ); ?>">
							<span class="itwt-tool-card__title"><?php esc_html_e( 'พิมพ์สติ๊กเกอร์ QR', 'it-warranty-tracker' ); ?></span>
							<span class="itwt-tool-card__hint"><?php esc_html_e( 'เปิดหน้าพิมพ์ป้ายสำหรับติดเครื่อง', 'it-warranty-tracker' ); ?></span>
						</a>
					<?php endif; ?>
					<a class="itwt-tool-card" href="<?php echo esc_url( \ITWT\Settings::lookup_url( (string) $asset->service_tag ) ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="itwt-tool-card__title"><?php esc_html_e( 'เปิดหน้าเช็คประกัน', 'it-warranty-tracker' ); ?></span>
						<span class="itwt-tool-card__hint"><?php esc_html_e( 'ดูมุมมองลูกค้าบนหน้าเว็บ', 'it-warranty-tracker' ); ?></span>
					</a>
				</div>

				<?php if ( $can_manage ) : ?>
					<form method="post" class="itwt-danger-zone" data-itwt-confirm-form="delete">
						<div class="itwt-danger-zone__copy">
							<h3><?php esc_html_e( 'ลบ / เลิกใช้งาน', 'it-warranty-tracker' ); ?></h3>
							<p><?php esc_html_e( 'ถ้ามีสัญญาหรือประวัติบริการ ระบบจะเปลี่ยนสถานะเป็นเลิกใช้งานแทนการลบ', 'it-warranty-tracker' ); ?></p>
						</div>
						<?php wp_nonce_field( 'itwt_delete_asset' ); ?>
						<input type="hidden" name="itwt_action" value="delete_asset" />
						<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />
						<button type="submit" class="itwt-action itwt-action--danger"><?php esc_html_e( 'ลบ / เลิกใช้งาน', 'it-warranty-tracker' ); ?></button>
					</form>
				<?php endif; ?>
			</div>

			<?php if ( $can_manage ) : ?>
				<div class="itwt-modal" id="itwt-contract-modal" hidden>
					<button type="button" class="itwt-modal__backdrop" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>"></button>
					<div class="itwt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-contract-modal-title">
						<header class="itwt-modal__header">
							<h2
								id="itwt-contract-modal-title"
								data-create-label="<?php echo esc_attr( sprintf( /* translators: %s: contract type */ __( 'เพิ่มสัญญา %s', 'it-warranty-tracker' ), Labels::contract_type( 'ma' ) ) ); ?>"
								data-edit-label="<?php esc_attr_e( 'แก้ไขสัญญา', 'it-warranty-tracker' ); ?>"
							><?php echo esc_html( sprintf( /* translators: %s: contract type label */ __( 'เพิ่มสัญญา %s', 'it-warranty-tracker' ), Labels::contract_type( 'ma' ) ) ); ?></h2>
							<button type="button" class="itwt-modal__close" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>">&times;</button>
						</header>
						<form method="post" class="itwt-modal__form" id="itwt-contract-form">
							<?php wp_nonce_field( 'itwt_save_contract' ); ?>
							<input type="hidden" name="itwt_action" value="save_contract" />
							<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />
							<input type="hidden" name="contract_id" id="contract_id" value="0" />
							<div class="itwt-modal__body">
								<?php
								$renew_hints = array();
								if ( ! empty( $asset->renew_max_years_enabled ) && ! empty( $asset->purchase_date ) && ! empty( $asset->renew_max_years ) ) {
									$limit_dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $asset->purchase_date, wp_timezone() );
									if ( $limit_dt ) {
										$limit_end = $limit_dt->modify( '+' . (int) $asset->renew_max_years . ' years' )->format( 'Y-m-d' );
										$renew_hints[] = sprintf(
											/* translators: 1: years, 2: formatted date */
											__( 'จำกัดปี: รวมไม่เกิน %1$d ปีจากวันซื้อ (ถึง %2$s)', 'it-warranty-tracker' ),
											(int) $asset->renew_max_years,
											Dates::format( $limit_end )
										);
									}
								}
								if ( ! empty( $asset->renew_max_times_enabled ) && ! empty( $asset->renew_max_times ) ) {
									$renew_hints[] = sprintf(
										/* translators: 1: used, 2: max */
										__( 'จำกัดครั้ง: ใช้ไปแล้ว %1$d / %2$d ครั้ง', 'it-warranty-tracker' ),
										$ma_count,
										(int) $asset->renew_max_times
									);
								}
								?>
								<p class="itwt-inline-notice" id="itwt-contract-renew-hints" <?php echo empty( $renew_hints ) ? 'hidden' : ''; ?>>
									<?php echo esc_html( implode( ' · ', $renew_hints ) ); ?>
								</p>
								<div class="itwt-modal__grid">
									<label for="contract_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
									<select id="contract_type" name="contract_type" required>
										<?php foreach ( Labels::contract_types() as $ct => $ct_label ) : ?>
											<option value="<?php echo esc_attr( $ct ); ?>"><?php echo esc_html( $ct_label ); ?></option>
										<?php endforeach; ?>
									</select>

									<label for="contract_no"><?php esc_html_e( 'เลขที่สัญญา', 'it-warranty-tracker' ); ?></label>
									<input type="text" id="contract_no" name="contract_no" />

									<label for="contract_start"><?php esc_html_e( 'วันเริ่ม', 'it-warranty-tracker' ); ?></label>
									<?php
									Dates::render_input(
										array(
											'id'       => 'contract_start',
											'name'     => 'start_date',
											'required' => true,
										)
									);
									?>

									<label for="contract_end"><?php esc_html_e( 'วันสิ้นสุด', 'it-warranty-tracker' ); ?></label>
									<?php
									Dates::render_input(
										array(
											'id'       => 'contract_end',
											'name'     => 'end_date',
											'required' => true,
										)
									);
									?>

									<span class="itwt-modal__label"><?php esc_html_e( 'คำนวณสิ้นสุด', 'it-warranty-tracker' ); ?></span>
									<div class="itwt-modal__chips">
										<button type="button" class="itwt-action itwt-contract-years" data-years="1"><?php esc_html_e( '1 ปี', 'it-warranty-tracker' ); ?></button>
										<button type="button" class="itwt-action itwt-contract-years" data-years="2"><?php esc_html_e( '2 ปี', 'it-warranty-tracker' ); ?></button>
										<button type="button" class="itwt-action itwt-contract-years" data-years="3"><?php esc_html_e( '3 ปี', 'it-warranty-tracker' ); ?></button>
									</div>

									<label for="contract_sla"><?php esc_html_e( 'SLA', 'it-warranty-tracker' ); ?></label>
									<input type="text" id="contract_sla" name="sla_tier" placeholder="On-Site 24x7 / NBD / Carry-in" />

									<label for="contract_status" class="itwt-contract-status-field" hidden><?php esc_html_e( 'สถานะ', 'it-warranty-tracker' ); ?></label>
									<select id="contract_status" name="status" class="itwt-contract-status-field" hidden disabled>
										<option value="active"><?php echo esc_html( Labels::contract_status( 'active' ) ); ?></option>
										<option value="cancelled"><?php echo esc_html( Labels::contract_status( 'cancelled' ) ); ?></option>
									</select>
								</div>
							</div>
							<footer class="itwt-modal__footer">
								<button type="button" class="itwt-action" data-itwt-modal-close><?php esc_html_e( 'ยกเลิก', 'it-warranty-tracker' ); ?></button>
								<button type="submit" class="itwt-action itwt-action--primary" name="submit"><?php esc_html_e( 'บันทึกสัญญา', 'it-warranty-tracker' ); ?></button>
							</footer>
						</form>
					</div>
				</div>

			<?php endif; ?>

			<?php if ( $can_log ) : ?>
				<div class="itwt-modal" id="itwt-service-modal" hidden>
					<button type="button" class="itwt-modal__backdrop" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>"></button>
					<div class="itwt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="itwt-service-modal-title">
						<header class="itwt-modal__header">
							<h2
								id="itwt-service-modal-title"
								data-create-label="<?php esc_attr_e( 'เพิ่มบันทึกบริการ', 'it-warranty-tracker' ); ?>"
								data-edit-label="<?php esc_attr_e( 'แก้ไขบันทึกบริการ', 'it-warranty-tracker' ); ?>"
							><?php esc_html_e( 'เพิ่มบันทึกบริการ', 'it-warranty-tracker' ); ?></h2>
							<button type="button" class="itwt-modal__close" data-itwt-modal-close aria-label="<?php esc_attr_e( 'ปิด', 'it-warranty-tracker' ); ?>">&times;</button>
						</header>
						<form method="post" class="itwt-modal__form" id="itwt-service-form">
							<?php wp_nonce_field( 'itwt_save_service_log' ); ?>
							<input type="hidden" name="itwt_action" value="save_service_log" />
							<input type="hidden" name="asset_id" value="<?php echo esc_attr( (string) $asset_id ); ?>" />
							<input type="hidden" name="log_id" id="service_log_id" value="0" />
							<div class="itwt-modal__body">
								<div class="itwt-modal__grid">
									<label for="service_date"><?php esc_html_e( 'วันที่', 'it-warranty-tracker' ); ?></label>
									<?php
									Dates::render_input(
										array(
											'id'       => 'service_date',
											'name'     => 'service_date',
											'value'    => wp_date( 'Y-m-d' ),
											'required' => true,
										)
									);
									?>

									<label for="service_type"><?php esc_html_e( 'ประเภท', 'it-warranty-tracker' ); ?></label>
									<select id="service_type" name="service_type">
										<?php foreach ( Labels::service_types() as $st => $st_label ) : ?>
											<option value="<?php echo esc_attr( $st ); ?>"><?php echo esc_html( $st_label ); ?></option>
										<?php endforeach; ?>
									</select>

									<label for="symptom"><?php esc_html_e( 'อาการ', 'it-warranty-tracker' ); ?></label>
									<textarea id="symptom" name="symptom" rows="3"></textarea>

									<label for="resolution"><?php esc_html_e( 'การแก้ไข', 'it-warranty-tracker' ); ?></label>
									<textarea id="resolution" name="resolution" rows="3"></textarea>

									<label for="public_summary"><?php esc_html_e( 'สรุปสาธารณะ', 'it-warranty-tracker' ); ?></label>
									<input type="text" id="public_summary" name="public_summary" maxlength="500" placeholder="<?php esc_attr_e( 'ว่าง = ไม่แสดงหน้าเว็บ', 'it-warranty-tracker' ); ?>" />
								</div>
							</div>
							<footer class="itwt-modal__footer">
								<button type="button" class="itwt-action" data-itwt-modal-close><?php esc_html_e( 'ยกเลิก', 'it-warranty-tracker' ); ?></button>
								<button type="submit" class="itwt-action itwt-action--primary" name="submit"><?php esc_html_e( 'บันทึกบริการ', 'it-warranty-tracker' ); ?></button>
							</footer>
						</form>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</section>

	<?php if ( $can_manage ) : ?>
		<?php
		$customer            = null;
		$edit_id             = 0;
		$modal_title         = __( 'เพิ่มรายชื่อ', 'it-warranty-tracker' );
		include ITWT_PLUGIN_DIR . 'admin/views/partials/customer-modal.php';
		?>
	<?php endif; ?>
</div>
