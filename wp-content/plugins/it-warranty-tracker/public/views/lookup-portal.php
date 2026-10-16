<?php
/**
 * Public warranty lookup portal markup.
 *
 * @package ITWT
 * @var string $consent_text
 * @var string $initial_tag
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="itwt-portal" id="itwt-portal" data-initial-tag="<?php echo esc_attr( $initial_tag ); ?>">
	<form class="itwt-search" id="itwt-search-form" role="search">
		<label class="itwt-search__label" for="itwt-q"><?php esc_html_e( 'Service Tag หรือ Serial Number', 'it-warranty-tracker' ); ?></label>
		<div class="itwt-search__row">
			<input
				type="search"
				id="itwt-q"
				name="q"
				class="itwt-search__input"
				value="<?php echo esc_attr( $initial_tag ); ?>"
				autocomplete="off"
				required
			/>
			<button type="submit" class="itwt-btn itwt-btn--primary" id="itwt-search-btn">
				<?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?>
			</button>
		</div>
	</form>

	<div class="itwt-status" id="itwt-status" hidden role="status" aria-live="polite"></div>
	<div class="itwt-result" id="itwt-result" hidden></div>

	<dialog class="itwt-dialog" id="itwt-support-dialog">
		<form method="dialog" class="itwt-dialog__form" id="itwt-support-form">
			<h2 class="itwt-dialog__title" id="itwt-support-title"><?php esc_html_e( 'แจ้งซ่อม / ขอต่อ MA', 'it-warranty-tracker' ); ?></h2>
			<input type="hidden" name="service_tag" id="itwt-support-tag" value="" />
			<input type="hidden" name="form_started" id="itwt-form-started" value="" />
			<p class="itwt-hp" aria-hidden="true">
				<label>Website <input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
			</p>
			<p>
				<label for="itwt-request-type"><?php esc_html_e( 'ประเภทคำขอ', 'it-warranty-tracker' ); ?></label>
				<select id="itwt-request-type" name="request_type" required>
					<option value="repair"><?php esc_html_e( 'แจ้งซ่อม', 'it-warranty-tracker' ); ?></option>
					<option value="renew_ma"><?php esc_html_e( 'ขอต่อ MA', 'it-warranty-tracker' ); ?></option>
				</select>
			</p>
			<p>
				<label for="itwt-contact-name"><?php esc_html_e( 'ชื่อผู้ติดต่อ', 'it-warranty-tracker' ); ?></label>
				<input type="text" id="itwt-contact-name" name="contact_name" required />
			</p>
			<p>
				<label for="itwt-phone"><?php esc_html_e( 'เบอร์โทร', 'it-warranty-tracker' ); ?></label>
				<input type="tel" id="itwt-phone" name="phone" required />
			</p>
			<p>
				<label for="itwt-detail"><?php esc_html_e( 'รายละเอียด', 'it-warranty-tracker' ); ?></label>
				<textarea id="itwt-detail" name="detail" rows="4" required></textarea>
			</p>
			<p>
				<label class="itwt-consent">
					<input type="checkbox" name="consent" id="itwt-consent" value="1" required />
					<span><?php echo esc_html( $consent_text ); ?></span>
				</label>
			</p>
			<div class="itwt-dialog__actions">
				<button type="submit" class="itwt-btn itwt-btn--primary" value="submit"><?php esc_html_e( 'ส่งคำขอ', 'it-warranty-tracker' ); ?></button>
				<button type="submit" class="itwt-btn itwt-btn--ghost" value="cancel"><?php esc_html_e( 'ปิด', 'it-warranty-tracker' ); ?></button>
			</div>
			<p class="itwt-dialog__msg" id="itwt-support-msg" hidden></p>
		</form>
	</dialog>
</div>
