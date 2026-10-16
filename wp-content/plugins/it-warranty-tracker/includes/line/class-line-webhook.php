<?php
/**
 * LINE webhook handler.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Line;

defined( 'ABSPATH' ) || exit;

use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Subscription_Repo;
use ITWT\Settings;

/**
 * Verifies signature and processes LINE events.
 */
final class Line_Webhook {

	/**
	 * Handle incoming webhook request.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( \WP_REST_Request $request ) {
		$body      = $request->get_body();
		$signature = (string) $request->get_header( 'x-line-signature' );

		if ( ! $this->verify_signature( $body, $signature ) ) {
			return new \WP_Error( 'itwt_line_sig', __( 'ลายเซ็นไม่ถูกต้อง', 'it-warranty-tracker' ), array( 'status' => 403 ) );
		}

		$payload = json_decode( $body, true );
		if ( ! is_array( $payload ) || empty( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
			return new \WP_REST_Response( array( 'ok' => true ), 200 );
		}

		$client = new Line_Client();

		foreach ( $payload['events'] as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}
			$this->process_event( $event, $client );
		}

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}

	/**
	 * Verify X-Line-Signature.
	 *
	 * @param string $body      Raw body.
	 * @param string $signature Header value.
	 */
	public function verify_signature( string $body, string $signature ): bool {
		if ( ! defined( 'ITWT_LINE_CHANNEL_SECRET' ) || '' === (string) ITWT_LINE_CHANNEL_SECRET ) {
			return false;
		}
		if ( '' === $signature ) {
			return false;
		}
		$hash = base64_encode( hash_hmac( 'sha256', $body, (string) ITWT_LINE_CHANNEL_SECRET, true ) );
		return hash_equals( $hash, $signature );
	}

	/**
	 * Process a single event.
	 *
	 * @param array<string,mixed> $event  Event payload.
	 * @param Line_Client         $client API client.
	 */
	private function process_event( array $event, Line_Client $client ): void {
		$type = (string) ( $event['type'] ?? '' );

		if ( 'unfollow' === $type ) {
			$user = (string) ( $event['source']['userId'] ?? '' );
			if ( '' !== $user ) {
				( new Subscription_Repo() )->unsubscribe_user( $user );
			}
			return;
		}

		if ( 'join' === $type ) {
			$group_id = (string) ( $event['source']['groupId'] ?? '' );
			$this->handle_join( $group_id, $client );
			return;
		}

		if ( 'message' !== $type ) {
			return;
		}

		$reply_token = (string) ( $event['replyToken'] ?? '' );
		$user_id     = (string) ( $event['source']['userId'] ?? '' );
		$text        = '';
		if ( isset( $event['message']['type'] ) && 'text' === $event['message']['type'] ) {
			$text = trim( (string) ( $event['message']['text'] ?? '' ) );
		}

		if ( '' === $reply_token || '' === $user_id ) {
			return;
		}

		if ( $this->is_cancel_command( $text ) ) {
			( new Subscription_Repo() )->unsubscribe_user( $user_id );
			$client->reply( $reply_token, array( Line_Client::text_message( __( 'ยกเลิกการรับแจ้งเตือนเรียบร้อยแล้ว', 'it-warranty-tracker' ) ) ) );
			return;
		}

		$code = $this->extract_link_code( $text );
		if ( null === $code ) {
			$client->reply(
				$reply_token,
				array(
					Line_Client::text_message(
						__( 'พิมพ์ "ผูกแจ้งเตือน XXXXXX" เพื่อผูกเครื่อง หรือพิมพ์ "ยกเลิก" เพื่อหยุดรับแจ้งเตือน', 'it-warranty-tracker' )
					),
				)
			);
			return;
		}

		$asset_id = get_transient( 'itwt_link_' . $code );
		if ( false === $asset_id ) {
			$client->reply( $reply_token, array( Line_Client::text_message( __( 'รหัสหมดอายุหรือไม่ถูกต้อง กรุณาขอรหัสใหม่จากหน้าเว็บ', 'it-warranty-tracker' ) ) ) );
			return;
		}

		$asset_id = (int) $asset_id;
		$asset    = ( new Asset_Repo() )->find( $asset_id );
		if ( ! $asset ) {
			$client->reply( $reply_token, array( Line_Client::text_message( __( 'ไม่พบทรัพย์สินที่ต้องการผูก', 'it-warranty-tracker' ) ) ) );
			return;
		}

		$result = ( new Subscription_Repo() )->subscribe( $asset_id, $user_id );
		delete_transient( 'itwt_link_' . $code );

		if ( is_wp_error( $result ) ) {
			$client->reply( $reply_token, array( Line_Client::text_message( __( 'ผูกแจ้งเตือนไม่สำเร็จ กรุณาลองใหม่', 'it-warranty-tracker' ) ) ) );
			return;
		}

		$settings = Settings::get();
		$pdpa     = (string) ( $settings['line_pdpa_text'] ?? '' );
		$msg      = sprintf(
			/* translators: 1: service tag, 2: model */
			__( "ผูกแจ้งเตือนสำเร็จ\nService Tag: %1\$s\nรุ่น: %2\$s", 'it-warranty-tracker' ),
			(string) $asset->service_tag,
			(string) $asset->model_name
		);
		if ( '' !== $pdpa ) {
			$msg .= "\n\n" . $pdpa;
		}

		$client->reply( $reply_token, array( Line_Client::text_message( $msg ) ) );
	}

	/**
	 * Handle bot joining a group.
	 *
	 * @param string      $group_id Group ID.
	 * @param Line_Client $client   Client.
	 */
	private function handle_join( string $group_id, Line_Client $client ): void {
		if ( '' === $group_id ) {
			return;
		}

		$settings  = Settings::get();
		$confirmed = (string) ( $settings['line_tech_group_id'] ?? '' );

		if ( '' !== $confirmed && $confirmed === $group_id ) {
			return;
		}

		if ( '' === $confirmed ) {
			Settings::update( array( 'line_pending_group_id' => $group_id ) );
			return;
		}

		// Not the confirmed tech group — leave automatically.
		$client->leave_group( $group_id );
	}

	/**
	 * Detect cancel command.
	 *
	 * @param string $text Message text.
	 */
	private function is_cancel_command( string $text ): bool {
		$normalized = mb_strtolower( trim( $text ) );
		return in_array( $normalized, array( 'ยกเลิก', 'cancel', 'unsubscribe' ), true );
	}

	/**
	 * Extract 6-char link code from message.
	 *
	 * @param string $text Message text.
	 */
	private function extract_link_code( string $text ): ?string {
		if ( preg_match( '/ผูกแจ้งเตือน[\s:]*([A-Z0-9]{6})/iu', $text, $m ) ) {
			return strtoupper( $m[1] );
		}
		if ( preg_match( '/\b([A-Z0-9]{6})\b/', strtoupper( $text ), $m ) ) {
			return $m[1];
		}
		return null;
	}
}
