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
	 * @return string
	 */
	public function render(): string {
		$this->enqueue();

		$settings = Settings::get();
		$tag      = isset( $_GET['tag'] ) ? Service_Tag::normalize( sanitize_text_field( wp_unslash( (string) $_GET['tag'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		ob_start();
		$consent_text = (string) ( $settings['pdpa_consent_text'] ?? '' );
		$initial_tag  = $tag;
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
				'i18n'        => array(
					'search'       => __( 'ค้นหา', 'it-warranty-tracker' ),
					'loading'      => __( 'กำลังค้นหา…', 'it-warranty-tracker' ),
					'notFound'     => __( 'ไม่พบข้อมูล', 'it-warranty-tracker' ),
					'rateLimited'  => __( 'ค้นหาบ่อยเกินไป กรุณารอสักครู่', 'it-warranty-tracker' ),
					'error'        => __( 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'it-warranty-tracker' ),
					'repair'       => __( 'แจ้งซ่อม', 'it-warranty-tracker' ),
					'renewMa'      => __( 'ขอต่อ MA', 'it-warranty-tracker' ),
					'lineNotify'   => __( 'รับแจ้งเตือนประกันทาง LINE', 'it-warranty-tracker' ),
					'submit'       => __( 'ส่งคำขอ', 'it-warranty-tracker' ),
					'chooseAsset'  => __( 'พบหลายรายการ กรุณาเลือก', 'it-warranty-tracker' ),
				),
			)
		);
	}
}
