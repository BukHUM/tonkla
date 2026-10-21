<?php
/**
 * IP-based rate limiter.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Rate limiting via object cache or transients.
 */
final class Rate_Limiter {

	/**
	 * Attempt to consume one request slot.
	 *
	 * @param string $bucket Unique bucket name (e.g. lookup).
	 * @param int    $limit  Max requests in the window.
	 * @param int    $window Window in seconds.
	 * @return bool True if allowed.
	 */
	public static function allow( string $bucket, int $limit, int $window ): bool {
		$ip  = self::client_ip();
		$key = 'itwt_rl_' . md5( $bucket . '|' . $ip );

		$count = wp_cache_get( $key, 'itwt' );
		if ( false === $count ) {
			$transient = get_transient( $key );
			$count     = false === $transient ? 0 : (int) $transient;
		} else {
			$count = (int) $count;
		}

		if ( $count >= $limit ) {
			return false;
		}

		++$count;
		wp_cache_set( $key, $count, 'itwt', $window );
		set_transient( $key, $count, $window );

		return true;
	}

	/**
	 * Resolve client IP.
	 */
	public static function client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';

		if ( defined( 'ITWT_TRUST_CLOUDFLARE' ) && ITWT_TRUST_CLOUDFLARE
			&& ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] )
		) {
			$cf = sanitize_text_field( (string) wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
				$ip = $cf;
			}
		}

		$ip = sanitize_text_field( $ip );
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}
}
