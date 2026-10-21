<?php
/**
 * Public warranty lookup shortcode.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Renders [itwt_warranty_lookup] and enqueues assets.
 */
final class Shortcode {

	/**
	 * Whether assets were enqueued this request.
	 *
	 * @var bool
	 */
	private bool $enqueued = false;

	/**
	 * Register shortcode.
	 */
	public function register(): void {
		add_shortcode( 'itwt_warranty_lookup', array( $this, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array<string,string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts = array() ): string {
		$this->enqueue();

		$settings = Settings::get();
		$atts     = shortcode_atts(
			array(
				'title'  => (string) ( $settings['portal_title'] ?? '' ),
				'desc'   => (string) ( $settings['portal_description'] ?? '' ),
				'accent' => (string) ( $settings['portal_accent_color'] ?? '' ),
			),
			is_array( $atts ) ? $atts : array(),
			'itwt_warranty_lookup'
		);

		$tag = isset( $_GET['tag'] ) ? Service_Tag::normalize( sanitize_text_field( wp_unslash( (string) $_GET['tag'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$logo_id   = (int) ( $settings['logo_attachment_id'] ?? 0 );
		$logo_url  = $logo_id > 0 ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		$phone     = (string) ( $settings['support_phone'] ?? '' );
		$privacy   = (string) ( $settings['privacy_policy_url'] ?? '' );
		$accent    = sanitize_hex_color( $atts['accent'] ) ?: '';

		ob_start();
		$consent_text       = (string) ( $settings['pdpa_consent_text'] ?? '' );
		$initial_tag        = $tag;
		$portal_title       = sanitize_text_field( $atts['title'] );
		$portal_description = sanitize_textarea_field( $atts['desc'] );
		$portal_logo_url    = is_string( $logo_url ) ? $logo_url : '';
		$portal_phone       = $phone;
		$portal_privacy     = $privacy;
		$portal_accent      = $accent;
		$portal_theme       = sanitize_key( (string) ( $settings['portal_theme'] ?? 'soft' ) );
		if ( ! in_array( $portal_theme, array( 'soft', 'minimal', 'dense' ), true ) ) {
			$portal_theme = 'soft';
		}
		$portal_layout = Page_Template::is_warranty_page() ? 'page' : 'embed';
		include ITWT_PLUGIN_DIR . 'public/views/lookup-portal.php';
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue public CSS/JS once.
	 */
	private function enqueue(): void {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;

		wp_enqueue_style(
			'itwt-warranty-portal',
			ITWT_PLUGIN_URL . 'public/css/warranty-portal.css',
			array(),
			ITWT_VERSION
		);

		wp_enqueue_script(
			'itwt-warranty-lookup',
			ITWT_PLUGIN_URL . 'public/js/warranty-lookup.js',
			array(),
			ITWT_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		$settings = Settings::get();
		wp_localize_script(
			'itwt-warranty-lookup',
			'itwtLookup',
			array(
				'restUrl'     => esc_url_raw( rest_url( 'itwt/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'consentText' => (string) ( $settings['pdpa_consent_text'] ?? '' ),
				'display'     => array(
					'showSupport'     => ! empty( $settings['show_support_button'] ),
					'showLine'        => ! empty( $settings['show_line_button'] ),
					'showPurchase'    => ! empty( $settings['show_purchase_date'] ),
					'showWarrantyEnd' => ! empty( $settings['show_warranty_end'] ),
					'showSla'         => ! empty( $settings['show_sla'] ),
					'showCoverageBar' => ! empty( $settings['show_coverage_bar'] ),
				),
				'i18n'        => array(
					'search'         => __( 'ค้นหา', 'tonkla-warranty-tracker' ),
					'loading'        => __( 'กำลังค้นหา…', 'tonkla-warranty-tracker' ),
					'notFound'       => __( 'ไม่พบข้อมูล — ตรวจ Service Tag หรือ Serial Number อีกครั้ง', 'tonkla-warranty-tracker' ),
					'rateLimited'    => __( 'ค้นหาบ่อยเกินไป กรุณารอสักครู่', 'tonkla-warranty-tracker' ),
					'error'          => __( 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'tonkla-warranty-tracker' ),
					'repair'         => __( 'แจ้งซ่อม', 'tonkla-warranty-tracker' ),
					'renewMa'        => __( 'ขอต่อ MA', 'tonkla-warranty-tracker' ),
					'lineNotify'     => __( 'รับแจ้งเตือนประกันทาง LINE', 'tonkla-warranty-tracker' ),
					'submit'         => __( 'ส่งคำขอ', 'tonkla-warranty-tracker' ),
					'chooseAsset'    => __( 'พบหลายรายการ กรุณาเลือก', 'tonkla-warranty-tracker' ),
					'specs'          => __( 'สเปก', 'tonkla-warranty-tracker' ),
					'contracts'      => __( 'สัญญา MA', 'tonkla-warranty-tracker' ),
					'contractsEmpty'    => __( 'ยังไม่มีสัญญา MA', 'tonkla-warranty-tracker' ),
					'serviceLogs'       => __( 'ประวัติบริการ', 'tonkla-warranty-tracker' ),
					'emptyHint'         => __( 'สแกน QR บนสติ๊กเกอร์ หรือกรอก Service Tag / Serial Number', 'tonkla-warranty-tracker' ),
					'purchaseDate'      => __( 'วันซื้อ', 'tonkla-warranty-tracker' ),
					'coverageUntil'     => __( 'คุ้มครองถึง', 'tonkla-warranty-tracker' ),
					'coverageStarts'    => __( 'เริ่มคุ้มครอง', 'tonkla-warranty-tracker' ),
					'warrantyEnd'       => __( 'หมดประกันเมื่อ', 'tonkla-warranty-tracker' ),
					'daysLeft'          => __( 'วันคงเหลือ', 'tonkla-warranty-tracker' ),
					'serial'            => __( 'S/N', 'tonkla-warranty-tracker' ),
					'sla'               => __( 'SLA', 'tonkla-warranty-tracker' ),
					'slaService'        => __( 'เงื่อนไขบริการ', 'tonkla-warranty-tracker' ),
					'renewHint'         => __( 'สามารถต่อประกันได้เมื่อหมดระยะประกัน', 'tonkla-warranty-tracker' ),
					'renewHintBlocked'  => __( 'ไม่สามารถต่อประกันได้', 'tonkla-warranty-tracker' ),
				),
			)
		);
	}
}
