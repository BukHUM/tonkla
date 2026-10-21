<?php
/**
 * QR sticker print view (warranty card layout).
 *
 * @package ITWT
 * @var array            $assets
 * @var array            $settings
 * @var array<int,array> $label_payloads
 */

defined( 'ABSPATH' ) || exit;

$size = isset( $_GET['size'] ) ? sanitize_key( wp_unslash( (string) $_GET['size'] ) ) : '90x55'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $size, array( '90x55', '50x30', '60x40' ), true ) ) {
	$size = '90x55';
}
if ( ! isset( $label_payloads ) || ! is_array( $label_payloads ) ) {
	$label_payloads = array();
}
?>
<div class="wrap itwt-wrap itwt-print-wrap">
	<header class="itwt-page-header itwt-no-print">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'tonkla-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'พิมพ์สติ๊กเกอร์ QR', 'tonkla-warranty-tracker' ); ?></h1>
		</div>
	</header>

	<form method="get" class="itwt-no-print itwt-settings-card itwt-print-controls">
		<input type="hidden" name="page" value="itwt-print-label" />
		<h2><?php esc_html_e( 'ตัวเลือกการพิมพ์', 'tonkla-warranty-tracker' ); ?></h2>
		<p class="description"><?php esc_html_e( 'สั่งพิมพ์ที่ขนาดจริง 100% (Actual size) — ห้ามเลือก Fit to page · ตัดตามเส้นประ', 'tonkla-warranty-tracker' ); ?></p>
		<div class="itwt-print-controls__body" data-itwt-print-picker>
			<input
				type="hidden"
				id="itwt-print-ids"
				name="ids"
				value="<?php echo esc_attr( implode( ',', array_map( static fn( $a ) => (string) (int) $a->id, $assets ) ) ); ?>"
			/>
			<div class="itwt-print-picker__chips" data-itwt-print-chips><?php
			foreach ( $assets as $asset ) :
				$chip_tag   = (string) ( $asset->service_tag ?? '' );
				$chip_brand = (string) ( $asset->brand ?? '' );
				$chip_model = (string) ( $asset->model_name ?? '' );
				$chip_parts = array_filter( array( $chip_brand, $chip_model ) );
				$chip_desc  = implode( ' ', $chip_parts );
				$chip_label = '' !== $chip_desc ? $chip_tag . ' — ' . $chip_desc : $chip_tag;
				?>
				<span
					class="itwt-print-picker__chip"
					data-id="<?php echo esc_attr( (string) (int) $asset->id ); ?>"
					data-label="<?php echo esc_attr( $chip_label ); ?>"
				>
					<code><?php echo esc_html( $chip_tag ); ?></code>
					<?php if ( '' !== $chip_desc ) : ?>
						<span class="itwt-print-picker__chip-desc"><?php echo esc_html( $chip_desc ); ?></span>
					<?php endif; ?>
					<button
						type="button"
						class="itwt-print-picker__chip-remove"
						data-itwt-print-remove
						aria-label="<?php esc_attr_e( 'ลบออก', 'tonkla-warranty-tracker' ); ?>"
					>&times;</button>
				</span>
				<?php
			endforeach;
			?></div>
			<div class="itwt-print-controls__row">
				<div class="itwt-print-field">
					<label for="itwt-print-asset-search"><?php esc_html_e( 'สินทรัพย์', 'tonkla-warranty-tracker' ); ?></label>
					<div class="itwt-combobox">
						<input
							type="search"
							id="itwt-print-asset-search"
							class="itwt-combobox__input"
							placeholder="<?php esc_attr_e( 'ค้นหา Service Tag, รุ่น หรือยี่ห้อ…', 'tonkla-warranty-tracker' ); ?>"
							autocomplete="off"
							aria-autocomplete="list"
							aria-controls="itwt-print-asset-results"
							aria-expanded="false"
						/>
						<ul id="itwt-print-asset-results" class="itwt-combobox__list" role="listbox" hidden></ul>
					</div>
				</div>
				<div class="itwt-print-field">
					<label for="itwt-print-size"><?php esc_html_e( 'ขนาด', 'tonkla-warranty-tracker' ); ?></label>
					<select id="itwt-print-size" name="size">
						<option value="90x55" <?php selected( $size, '90x55' ); ?>>90×55 มม. (<?php esc_html_e( 'มาตรฐาน', 'tonkla-warranty-tracker' ); ?>)</option>
						<option value="60x40" <?php selected( $size, '60x40' ); ?>>60×40 มม.</option>
						<option value="50x30" <?php selected( $size, '50x30' ); ?>>50×30 มม.</option>
					</select>
				</div>
			</div>
			<div class="itwt-print-controls__actions">
				<?php submit_button( __( 'โหลด', 'tonkla-warranty-tracker' ), 'secondary', '', false ); ?>
				<button type="button" class="button button-primary" onclick="window.print()"><?php esc_html_e( 'พิมพ์', 'tonkla-warranty-tracker' ); ?></button>
			</div>
		</div>
	</form>

	<div class="itwt-labels itwt-labels--<?php echo esc_attr( $size ); ?>" data-itwt-size="<?php echo esc_attr( $size ); ?>">
		<?php if ( empty( $label_payloads ) ) : ?>
			<p class="itwt-no-print"><?php esc_html_e( 'ค้นหาและเลือกสินทรัพย์ด้านบน แล้วกดโหลด', 'tonkla-warranty-tracker' ); ?></p>
		<?php else : ?>
			<?php foreach ( $label_payloads as $label ) : ?>
				<?php
				$logo_id   = (int) ( $label['logo_id'] ?? 0 );
				$logo_url  = (string) ( $label['logo_url'] ?? '' );
				$has_logo  = $logo_id > 0 || '' !== $logo_url;
				$has_title = '' !== (string) ( $label['title'] ?? '' );
				$logo_alt  = (string) ( $label['company_name_th'] ?? '' );
				if ( '' === $logo_alt ) {
					$logo_alt = (string) ( $label['company_name_en'] ?? 'Logo' );
				}
				?>
				<article class="itwt-label" data-qr-url="<?php echo esc_attr( (string) ( $label['qr_url'] ?? '' ) ); ?>">
					<header class="itwt-label__header">
						<div class="itwt-label__brand">
							<?php if ( $logo_id > 0 ) : ?>
								<?php
								echo wp_get_attachment_image(
									$logo_id,
									'medium',
									false,
									array(
										'class' => 'itwt-label__logo',
										'alt'   => $logo_alt,
									)
								);
								?>
							<?php elseif ( '' !== $logo_url ) : ?>
								<img
									class="itwt-label__logo"
									src="<?php echo esc_url( $logo_url ); ?>"
									alt="<?php echo esc_attr( $logo_alt ); ?>"
									loading="eager"
									decoding="sync"
								/>
							<?php else : ?>
								<div class="itwt-label__company">
									<?php if ( '' !== (string) ( $label['company_name_th'] ?? '' ) ) : ?>
										<span class="itwt-label__company-th"><?php echo esc_html( (string) $label['company_name_th'] ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== (string) ( $label['company_name_en'] ?? '' ) ) : ?>
										<span class="itwt-label__company-en"><?php echo esc_html( (string) $label['company_name_en'] ); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="itwt-label__meta">
							<span class="itwt-label__tag"><?php echo esc_html( (string) ( $label['service_tag'] ?? '' ) ); ?></span>
							<?php if ( '' !== (string) ( $label['type_label'] ?? '' ) ) : ?>
								<span class="itwt-label__type">
									<span class="itwt-label__type-icon">
										<?php
										echo wp_kses(
											(string) ( $label['type_icon_svg'] ?? '' ),
											array(
												'svg'  => array(
													'xmlns'       => true,
													'viewbox'     => true,
													'width'       => true,
													'height'      => true,
													'aria-hidden' => true,
													'focusable'   => true,
												),
												'path' => array(
													'fill' => true,
													'd'    => true,
												),
											)
										);
										?>
									</span>
									<span class="itwt-label__type-text"><?php echo esc_html( (string) $label['type_label'] ); ?></span>
								</span>
							<?php endif; ?>
						</div>
					</header>

					<div class="itwt-label__body">
						<div class="itwt-label__main">
							<?php if ( '' !== (string) ( $label['subtitle'] ?? '' ) ) : ?>
								<h2 class="itwt-label__heading"><?php echo esc_html( (string) $label['subtitle'] ); ?></h2>
							<?php endif; ?>

							<?php if ( '' !== (string) ( $label['purchase'] ?? '' ) || '' !== (string) ( $label['expire'] ?? '' ) ) : ?>
								<div class="itwt-label__dates">
									<div class="itwt-label__date">
										<span class="itwt-label__date-label"><?php esc_html_e( 'Purchase', 'tonkla-warranty-tracker' ); ?></span>
										<span class="itwt-label__date-value"><?php echo esc_html( (string) ( $label['purchase'] ?? '—' ) ); ?></span>
									</div>
									<div class="itwt-label__date">
										<span class="itwt-label__date-label"><?php esc_html_e( 'Expire', 'tonkla-warranty-tracker' ); ?></span>
										<span class="itwt-label__date-value"><?php echo esc_html( (string) ( $label['expire'] ?? '—' ) ); ?></span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $label['lines'] ) && is_array( $label['lines'] ) ) : ?>
								<ul class="itwt-label__lines">
									<?php foreach ( $label['lines'] as $line ) : ?>
										<li>
											<span class="itwt-label__line-key"><?php echo esc_html( (string) ( $line['key'] ?? '' ) ); ?></span>
											<span class="itwt-label__line-value"><?php echo esc_html( (string) ( $line['value'] ?? '' ) ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
						<div class="itwt-label__qr-wrap">
							<div class="itwt-label__qr" aria-hidden="true"></div>
							<span class="itwt-label__qr-caption"><?php esc_html_e( 'สแกนดูข้อมูล', 'tonkla-warranty-tracker' ); ?></span>
							<?php if ( $has_title ) : ?>
								<div class="itwt-label__host">
									<span class="itwt-label__host-key"><?php echo esc_html( '' !== (string) ( $label['title_key'] ?? '' ) ? (string) $label['title_key'] : __( 'ชื่อเครื่อง', 'tonkla-warranty-tracker' ) ); ?></span>
									<span class="itwt-label__host-value"><?php echo esc_html( (string) $label['title'] ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<p class="itwt-no-print itwt-print-footnote description">
		<?php esc_html_e( 'แนะนำพิมพ์บนสติ๊กเกอร์ขาวมันหรือ PVC แล้วเคลือบใสทับ · ทดสอบสแกน QR จากงานพิมพ์จริงก่อนแปะ', 'tonkla-warranty-tracker' ); ?>
	</p>
</div>
