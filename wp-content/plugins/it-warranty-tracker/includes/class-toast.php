<?php
/**
 * Admin toast notifications (top-right).
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Queues and renders ephemeral toasts for ITWT admin screens.
 */
final class Toast {

	/**
	 * Queued items.
	 *
	 * @var array<int,array{type:string,message:string}>
	 */
	private static array $queue = array();

	/**
	 * Whether footer hook is registered.
	 */
	private static bool $hooked = false;

	/**
	 * Queue a toast.
	 *
	 * @param string $type    success|error|info|warning.
	 * @param string $message Plain text message.
	 */
	public static function push( string $type, string $message ): void {
		$message = trim( wp_strip_all_tags( $message ) );
		if ( '' === $message ) {
			return;
		}
		$allowed = array( 'success', 'error', 'info', 'warning' );
		if ( ! in_array( $type, $allowed, true ) ) {
			$type = 'info';
		}
		self::$queue[] = array(
			'type'    => $type,
			'message' => $message,
		);
		self::ensure_hook();
	}

	/**
	 * Register admin_footer renderer once.
	 */
	private static function ensure_hook(): void {
		if ( self::$hooked ) {
			return;
		}
		self::$hooked = true;
		add_action( 'admin_footer', array( self::class, 'render' ), 5 );
	}

	/**
	 * Print toast host + queued payloads for JS.
	 */
	public static function render(): void {
		if ( empty( self::$queue ) ) {
			return;
		}
		$payload = wp_json_encode( self::$queue );
		if ( ! is_string( $payload ) ) {
			return;
		}
		echo '<div id="itwt-toast-host" class="itwt-toast-host" aria-live="polite" aria-relevant="additions"></div>';
		echo '<script type="application/json" id="itwt-toast-queue">' . $payload . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON from wp_json_encode.
		self::$queue = array();
	}
}
