<?php
/**
 * REST API routes for itwt/v1.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

use ITWT\Line\Line_Webhook;
use ITWT\Line\Tech_Notifier;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Contract_Repo;
use ITWT\Repositories\Service_Log_Repo;
use ITWT\Repositories\Support_Request_Repo;

/**
 * Registers and handles public REST endpoints.
 */
final class Rest_Api {

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'itwt/v1',
			'/lookup',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'lookup' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'itwt/v1',
			'/support-request',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'support_request' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'itwt/v1',
			'/line/link-code',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'link_code' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'itwt/v1',
			'/line/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( new Line_Webhook(), 'handle' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * GET /lookup?q=
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function lookup( \WP_REST_Request $request ) {
		if ( ! Rate_Limiter::allow( 'lookup', 15, MINUTE_IN_SECONDS ) ) {
			return new \WP_Error( 'itwt_rate', __( 'ค้นหาบ่อยเกินไป กรุณารอสักครู่', 'it-warranty-tracker' ), array( 'status' => 429 ) );
		}

		$q = Service_Tag::normalize( (string) $request->get_param( 'q' ) );
		if ( '' === $q ) {
			return new \WP_Error( 'itwt_invalid', __( 'กรุณาระบุ Service Tag หรือ Serial Number', 'it-warranty-tracker' ), array( 'status' => 400 ) );
		}

		$cache_key = 'itwt_lookup_' . md5( $q );
		$cached    = wp_cache_get( $cache_key, 'itwt' );
		if ( false !== $cached && is_array( $cached ) ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$repo   = new Asset_Repo();
		$result = $repo->lookup( $q );

		if ( 'none' === $result['type'] ) {
			$payload = array(
				'status'  => 'not_found',
				'message' => __( 'ไม่พบข้อมูล', 'it-warranty-tracker' ),
			);
			wp_cache_set( $cache_key, $payload, 'itwt', HOUR_IN_SECONDS );
			return new \WP_REST_Response( $payload, 200 );
		}

		if ( 'choices' === $result['type'] ) {
			$choices = array();
			foreach ( $result['assets'] as $asset ) {
				$choices[] = Public_Presenter::present_choice( $asset );
			}
			$payload = array(
				'status'  => 'choices',
				'choices' => $choices,
			);
			wp_cache_set( $cache_key, $payload, 'itwt', HOUR_IN_SECONDS );
			return new \WP_REST_Response( $payload, 200 );
		}

		$asset     = $result['assets'][0];
		$contracts = ( new Contract_Repo() )->for_asset( (int) $asset->id );
		$logs      = ( new Service_Log_Repo() )->for_asset( (int) $asset->id, true );
		$payload   = array(
			'status' => 'ok',
			'asset'  => Public_Presenter::present_asset( $asset, $contracts, $logs ),
		);
		wp_cache_set( $cache_key, $payload, 'itwt', HOUR_IN_SECONDS );
		return new \WP_REST_Response( $payload, 200 );
	}

	/**
	 * POST /support-request
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function support_request( \WP_REST_Request $request ) {
		if ( ! Rate_Limiter::allow( 'support', 3, HOUR_IN_SECONDS ) ) {
			return new \WP_Error( 'itwt_rate', __( 'ส่งคำขอบ่อยเกินไป กรุณารอสักครู่', 'it-warranty-tracker' ), array( 'status' => 429 ) );
		}

		$honeypot = (string) $request->get_param( 'website' );
		if ( '' !== $honeypot ) {
			return new \WP_REST_Response( array( 'ok' => true ), 200 );
		}

		$form_started = (int) $request->get_param( 'form_started' );
		if ( $form_started > 0 && ( time() - $form_started ) < 3 ) {
			return new \WP_Error( 'itwt_fast', __( 'กรุณาลองใหม่อีกครั้ง', 'it-warranty-tracker' ), array( 'status' => 400 ) );
		}

		$consent = (bool) $request->get_param( 'consent' );
		if ( ! $consent ) {
			return new \WP_Error( 'itwt_consent', __( 'ต้องยินยอมการเก็บข้อมูลก่อนส่งคำขอ', 'it-warranty-tracker' ), array( 'status' => 400 ) );
		}

		$tag   = Service_Tag::normalize( (string) $request->get_param( 'service_tag' ) );
		$asset = ( new Asset_Repo() )->find_by_tag( $tag );
		if ( ! $asset ) {
			return new \WP_Error( 'itwt_not_found', __( 'ไม่พบทรัพย์สิน', 'it-warranty-tracker' ), array( 'status' => 404 ) );
		}

		$result = ( new Support_Request_Repo() )->create(
			array(
				'asset_id'      => (int) $asset->id,
				'request_type'  => (string) $request->get_param( 'request_type' ),
				'contact_name'  => (string) $request->get_param( 'contact_name' ),
				'phone'         => (string) $request->get_param( 'phone' ),
				'detail'        => (string) $request->get_param( 'detail' ),
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		Tech_Notifier::schedule( (int) $result );

		return new \WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'รับคำขอเรียบร้อยแล้ว ทีมงานจะติดต่อกลับ', 'it-warranty-tracker' ),
			),
			201
		);
	}

	/**
	 * POST /line/link-code
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function link_code( \WP_REST_Request $request ) {
		if ( ! Rate_Limiter::allow( 'link_code', 5, HOUR_IN_SECONDS ) ) {
			return new \WP_Error( 'itwt_rate', __( 'ขอรหัสบ่อยเกินไป กรุณารอสักครู่', 'it-warranty-tracker' ), array( 'status' => 429 ) );
		}

		$tag   = Service_Tag::normalize( (string) $request->get_param( 'service_tag' ) );
		$asset = ( new Asset_Repo() )->find_by_tag( $tag );
		if ( ! $asset || 'active' !== $asset->status ) {
			return new \WP_Error( 'itwt_not_found', __( 'ไม่พบทรัพย์สิน', 'it-warranty-tracker' ), array( 'status' => 404 ) );
		}

		$code = strtoupper( wp_generate_password( 6, false, false ) );
		$code = preg_replace( '/[^A-Z0-9]/', '', $code ) ?? '';
		if ( strlen( $code ) < 6 ) {
			$code = strtoupper( substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 6 ) );
		}

		set_transient( 'itwt_link_' . $code, (int) $asset->id, 15 * MINUTE_IN_SECONDS );

		$settings = Settings::get();
		$oa_id    = (string) ( $settings['line_oa_id'] ?? '' );
		$message  = rawurlencode( 'ผูกแจ้งเตือน ' . $code );
		$deep     = '' !== $oa_id
			? 'https://line.me/R/oaMessage/' . rawurlencode( $oa_id ) . '/?' . $message
			: '';

		return new \WP_REST_Response(
			array(
				'code'      => $code,
				'deep_link' => $deep,
				'expires'   => 15 * MINUTE_IN_SECONDS,
			),
			200
		);
	}
}
