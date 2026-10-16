<?php
/**
 * QR sticker print view.
 *
 * @package ITWT
 * @var array $assets
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$phone = (string) ( $settings['support_phone'] ?? '' );
$logo_id = (int) ( $settings['logo_attachment_id'] ?? 0 );
$size = isset( $_GET['size'] ) ? sanitize_key( wp_unslash( (string) $_GET['size'] ) ) : '50x30'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $size, array( '50x30', '60x40' ), true ) ) {
	$size = '50x30';
}
?>
<div class="wrap itwt-wrap itwt-print-wrap">
	<header class="itwt-page-header itwt-no-print">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'it-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'พิมพ์สติ๊กเกอร์ QR', 'it-warranty-tracker' ); ?></h1>
		</div>
	</header>

	<form method="get" class="itwt-no-print itwt-settings-card itwt-print-controls">
		<input type="hidden" name="page" value="itwt-print-label" />
		<h2><?php esc_html_e( 'ตัวเลือกการพิมพ์', 'it-warranty-tracker' ); ?></h2>
		<div class="itwt-settings-grid">
			<label for="itwt-print-ids"><?php esc_html_e( 'Asset IDs (คั่นด้วยจุลภาค)', 'it-warranty-tracker' ); ?></label>
			<div><input type="text" id="itwt-print-ids" name="ids" value="<?php echo esc_attr( implode( ',', array_map( static fn( $a ) => (string) (int) $a->id, $assets ) ) ); ?>" class="regular-text" /></div>
			<label for="itwt-print-size"><?php esc_html_e( 'ขนาด', 'it-warranty-tracker' ); ?></label>
			<div>
				<select id="itwt-print-size" name="size">
					<option value="50x30" <?php selected( $size, '50x30' ); ?>>50×30 มม.</option>
					<option value="60x40" <?php selected( $size, '60x40' ); ?>>60×40 มม.</option>
				</select>
			</div>
		</div>
		<p>
			<?php submit_button( __( 'โหลด', 'it-warranty-tracker' ), 'secondary', '', false ); ?>
			<button type="button" class="button button-primary" onclick="window.print()"><?php esc_html_e( 'พิมพ์', 'it-warranty-tracker' ); ?></button>
		</p>
	</form>

	<div class="itwt-labels itwt-labels--<?php echo esc_attr( $size ); ?>" data-itwt-size="<?php echo esc_attr( $size ); ?>">
		<?php if ( empty( $assets ) ) : ?>
			<p class="itwt-no-print"><?php esc_html_e( 'ระบุ Asset IDs เพื่อพิมพ์สติ๊กเกอร์', 'it-warranty-tracker' ); ?></p>
		<?php else : ?>
			<?php foreach ( $assets as $asset ) : ?>
				<?php
				$url = \ITWT\Settings::lookup_url( (string) $asset->service_tag );
				?>
				<div class="itwt-label" data-qr-url="<?php echo esc_attr( $url ); ?>">
					<div class="itwt-label__brand">
						<?php
						if ( $logo_id > 0 ) {
							echo wp_get_attachment_image( $logo_id, 'thumbnail', false, array( 'class' => 'itwt-label__logo' ) );
						}
						?>
						<?php if ( '' !== $phone ) : ?>
							<span class="itwt-label__phone"><?php echo esc_html( $phone ); ?></span>
						<?php endif; ?>
					</div>
					<div class="itwt-label__qr" aria-hidden="true"></div>
					<div class="itwt-label__tag"><?php echo esc_html( (string) $asset->service_tag ); ?></div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
