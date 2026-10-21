<?php
/**
 * Public warranty lookup portal markup.
 *
 * @package ITWT
 * @var string $consent_text
 * @var string $initial_tag
 * @var string $portal_title
 * @var string $portal_description
 * @var string $portal_logo_url
 * @var string $portal_phone
 * @var string $portal_privacy
 * @var string $portal_accent
 * @var string $portal_theme
 * @var string $portal_layout page|embed
 */

defined( 'ABSPATH' ) || exit;

$portal_theme = isset( $portal_theme ) ? sanitize_key( (string) $portal_theme ) : 'soft';
if ( ! in_array( $portal_theme, array( 'soft', 'minimal', 'dense' ), true ) ) {
	$portal_theme = 'soft';
}
$portal_layout = isset( $portal_layout ) ? sanitize_key( (string) $portal_layout ) : 'embed';
if ( ! in_array( $portal_layout, array( 'page', 'embed' ), true ) ) {
	$portal_layout = 'embed';
}
$style_attr = '' !== $portal_accent ? '--itwt-accent:' . $portal_accent . ';' : '';
$show_brand = 'page' !== $portal_layout && ( $portal_logo_url || $portal_title || $portal_description );
?>
<div
	class="itwt-portal"
	id="itwt-portal"
	data-theme="<?php echo esc_attr( $portal_theme ); ?>"
	data-layout="<?php echo esc_attr( $portal_layout ); ?>"
	data-initial-tag="<?php echo esc_attr( $initial_tag ); ?>"
	<?php echo $style_attr ? 'style="' . esc_attr( $style_attr ) . '"' : ''; ?>
>
	<?php if ( $show_brand ) : ?>
		<header class="itwt-portal__brand">
			<?php if ( $portal_logo_url ) : ?>
				<img class="itwt-portal__logo" src="<?php echo esc_url( $portal_logo_url ); ?>" alt="" width="160" height="48" loading="lazy" />
			<?php endif; ?>
			<?php if ( $portal_title ) : ?>
				<h2 class="itwt-portal__title"><?php echo esc_html( $portal_title ); ?></h2>
			<?php endif; ?>
			<?php if ( $portal_description ) : ?>
				<p class="itwt-portal__desc"><?php echo esc_html( $portal_description ); ?></p>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<form class="itwt-search" id="itwt-search-form" role="search">
		<label class="itwt-search__label" for="itwt-q"><?php esc_html_e( 'Service Tag หรือ Serial Number', 'tonkla-warranty-tracker' ); ?></label>
		<div class="itwt-search__row">
			<input
				type="search"
				id="itwt-q"
				name="q"
				class="itwt-search__input"
				value="<?php echo esc_attr( $initial_tag ); ?>"
				autocomplete="off"
				placeholder="<?php esc_attr_e( 'เช่น ABC2345 หรือ Serial Number', 'tonkla-warranty-tracker' ); ?>"
				required
			/>
			<button type="submit" class="itwt-btn itwt-btn--primary" id="itwt-search-btn">
				<?php esc_html_e( 'ค้นหา', 'tonkla-warranty-tracker' ); ?>
			</button>
		</div>
	</form>

	<div class="itwt-status" id="itwt-status" hidden role="status" aria-live="polite"></div>
	<div class="itwt-skeleton" id="itwt-skeleton" hidden aria-hidden="true">
		<div class="itwt-skeleton__line"></div>
		<div class="itwt-skeleton__line itwt-skeleton__line--short"></div>
		<div class="itwt-skeleton__block"></div>
	</div>
	<div class="itwt-result" id="itwt-result" hidden></div>

	<?php
	$show_credit = \ITWT\Settings::show_developer_credit();
	if ( $portal_phone || $portal_privacy || $show_credit ) :
		?>
		<footer class="itwt-portal__footer">
			<?php if ( $portal_phone ) : ?>
				<p><?php esc_html_e( 'ศูนย์บริการ', 'tonkla-warranty-tracker' ); ?>: <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $portal_phone ) ?? '' ); ?>"><?php echo esc_html( $portal_phone ); ?></a></p>
			<?php endif; ?>
			<?php if ( $portal_privacy ) : ?>
				<p><a href="<?php echo esc_url( $portal_privacy ); ?>"><?php esc_html_e( 'นโยบายความเป็นส่วนตัว', 'tonkla-warranty-tracker' ); ?></a></p>
			<?php endif; ?>
			<?php \ITWT\Settings::render_developer_credit( 'portal' ); ?>
		</footer>
	<?php endif; ?>

	<dialog class="itwt-dialog" id="itwt-support-dialog">
		<form method="dialog" class="itwt-dialog__form" id="itwt-support-form">
			<header class="itwt-dialog__header">
				<h2 class="itwt-dialog__title" id="itwt-support-title"><?php esc_html_e( 'แจ้งซ่อม / ขอต่อ MA', 'tonkla-warranty-tracker' ); ?></h2>
			</header>
			<div class="itwt-dialog__body">
				<input type="hidden" name="service_tag" id="itwt-support-tag" value="" />
				<input type="hidden" name="form_started" id="itwt-form-started" value="" />
				<p class="itwt-hp" aria-hidden="true">
					<label>Website <input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
				</p>
				<div class="itwt-dialog__field">
					<label for="itwt-request-type"><?php esc_html_e( 'ประเภทคำขอ', 'tonkla-warranty-tracker' ); ?></label>
					<select id="itwt-request-type" name="request_type" required>
						<option value="repair"><?php esc_html_e( 'แจ้งซ่อม', 'tonkla-warranty-tracker' ); ?></option>
						<option value="renew_ma"><?php esc_html_e( 'ขอต่อ MA', 'tonkla-warranty-tracker' ); ?></option>
					</select>
				</div>
				<div class="itwt-dialog__field">
					<label for="itwt-contact-name"><?php esc_html_e( 'ชื่อผู้ติดต่อ', 'tonkla-warranty-tracker' ); ?></label>
					<input type="text" id="itwt-contact-name" name="contact_name" required />
				</div>
				<div class="itwt-dialog__field">
					<label for="itwt-phone"><?php esc_html_e( 'เบอร์โทร', 'tonkla-warranty-tracker' ); ?></label>
					<input type="tel" id="itwt-phone" name="phone" required />
				</div>
				<div class="itwt-dialog__field">
					<label for="itwt-detail"><?php esc_html_e( 'รายละเอียด', 'tonkla-warranty-tracker' ); ?></label>
					<textarea id="itwt-detail" name="detail" rows="4" required></textarea>
				</div>
				<label class="itwt-consent">
					<input type="checkbox" name="consent" id="itwt-consent" value="1" required />
					<span><?php echo esc_html( $consent_text ); ?></span>
				</label>
			</div>
			<footer class="itwt-dialog__footer">
				<button type="button" class="itwt-btn itwt-btn--ghost" data-itwt-dialog-close><?php esc_html_e( 'ปิด', 'tonkla-warranty-tracker' ); ?></button>
				<button type="submit" class="itwt-btn itwt-btn--primary"><?php esc_html_e( 'ส่งคำขอ', 'tonkla-warranty-tracker' ); ?></button>
			</footer>
			<p class="itwt-dialog__msg" id="itwt-support-msg" hidden></p>
		</form>
	</dialog>
</div>
