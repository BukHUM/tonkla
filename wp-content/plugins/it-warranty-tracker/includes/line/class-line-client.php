<?php
/**
 * LINE Messaging API client.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Line;

defined( 'ABSPATH' ) || exit;

/**
 * Thin wrapper around LINE Messaging API.
 */
final class Line_Client {

	private const API_BASE = 'https://api.line.me/v2/bot';

	/**
	 * Whether credentials are configured.
	 */
	public function is_configured(): bool {
		return defined( 'ITWT_LINE_CHANNEL_TOKEN' ) && '' !== (string) ITWT_LINE_CHANNEL_TOKEN
			&& defined( 'ITWT_LINE_CHANNEL_SECRET' ) && '' !== (string) ITWT_LINE_CHANNEL_SECRET;
	}

	/**
	 * Reply to a webhook event (does not count toward push quota).
	 *
	 * @param string               $reply_token Reply token.
	 * @param array<int,array>     $messages    LINE messages.
	 * @return true|\WP_Error
	 */
	public function reply( string $reply_token, array $messages ) {
		return $this->request(
			'/message/reply',
			array(
				'replyToken' => $reply_token,
				'messages'   => $messages,
			)
		);
	}

	/**
	 * Push message to a user or group.
	 *
	 * @param string           $to       User or group ID.
	 * @param array<int,array> $messages Messages.
	 * @return true|\WP_Error
	 */
	public function push( string $to, array $messages ) {
		return $this->request(
			'/message/push',
			array(
				'to'       => $to,
				'messages' => $messages,
			)
		);
	}

	/**
	 * Leave a group.
	 *
	 * @param string $group_id Group ID.
	 * @return true|\WP_Error
	 */
	public function leave_group( string $group_id ) {
		return $this->request( '/group/' . rawurlencode( $group_id ) . '/leave', array(), 'POST' );
	}

	/**
	 * Fetch quota consumption.
	 *
	 * @return array{totalUsage:int}|null
	 */
	public function quota_consumption(): ?array {
		$response = $this->request( '/message/quota/consumption', array(), 'GET' );
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return null;
		}
		return array(
			'totalUsage' => (int) ( $response['totalUsage'] ?? 0 ),
		);
	}

	/**
	 * Build a simple text message.
	 *
	 * @param string $text Message text.
	 * @return array<string,string>
	 */
	public static function text_message( string $text ): array {
		return array(
			'type' => 'text',
			'text' => mb_substr( $text, 0, 4900 ),
		);
	}

	/**
	 * Perform API request.
	 *
	 * @param string               $path   API path.
	 * @param array<string,mixed>  $body   JSON body (ignored for GET).
	 * @param string               $method HTTP method.
	 * @return true|array<string,mixed>|\WP_Error
	 */
	private function request( string $path, array $body = array(), string $method = 'POST' ) {
		if ( ! $this->is_configured() ) {
			return new \WP_Error( 'itwt_line_config', __( 'ยังไม่ได้ตั้งค่า LINE Channel', 'it-warranty-tracker' ) );
		}

		$url  = self::API_BASE . $path;
		$args = array(
			'method'  => $method,
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . (string) ITWT_LINE_CHANNEL_TOKEN,
				'Content-Type'  => 'application/json',
			),
		);

		if ( 'GET' !== $method ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$raw  = (string) wp_remote_retrieve_body( $response );
		$data = json_decode( $raw, true );

		if ( $code >= 200 && $code < 300 ) {
			if ( 'GET' === $method ) {
				return is_array( $data ) ? $data : array();
			}
			return true;
		}

		return new \WP_Error(
			'itwt_line_api',
			__( 'ส่งข้อความ LINE ไม่สำเร็จ', 'it-warranty-tracker' ),
			array( 'status' => $code )
		);
	}
}
