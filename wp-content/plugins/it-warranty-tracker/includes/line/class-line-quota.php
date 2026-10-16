<?php
/**
 * LINE monthly push quota management.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Line;

defined( 'ABSPATH' ) || exit;

use ITWT\Settings;

/**
 * Checks and reserves LINE push quota.
 */
final class Line_Quota {

	/**
	 * Get cached consumption + limits.
	 *
	 * @return array{used:int,quota:int,reserve:int,remaining:int,pct:float}
	 */
	public function snapshot(): array {
		$settings = Settings::get();
		$quota    = max( 1, (int) ( $settings['line_monthly_quota'] ?? 300 ) );
		$reserve  = max( 0, (int) ( $settings['line_customer_reserve'] ?? 150 ) );
		$used     = $this->get_used();

		return array(
			'used'      => $used,
			'quota'     => $quota,
			'reserve'   => $reserve,
			'remaining' => max( 0, $quota - $used ),
			'pct'       => $quota > 0 ? round( ( $used / $quota ) * 100, 1 ) : 0.0,
		);
	}

	/**
	 * Whether a customer push is allowed.
	 */
	public function can_push_customer(): bool {
		$snap = $this->snapshot();
		return $snap['remaining'] > 0;
	}

	/**
	 * Whether a tech-group push is allowed (respects customer reserve).
	 *
	 * @param int $estimated_cost Messages cost (group size).
	 */
	public function can_push_tech( int $estimated_cost = 5 ): bool {
		$snap = $this->snapshot();
		$ceiling = $snap['quota'] - $snap['reserve'];
		return ( $snap['used'] + $estimated_cost ) <= $ceiling;
	}

	/**
	 * Mark local counter after a successful push.
	 *
	 * @param int $count Messages consumed.
	 */
	public function record_push( int $count = 1 ): void {
		$key   = $this->counter_key();
		$used  = (int) get_transient( $key );
		set_transient( $key, $used + max( 1, $count ), MONTH_IN_SECONDS );
		wp_cache_delete( 'itwt_line_quota_api', 'itwt' );
	}

	/**
	 * Used count from API (cached 1h) plus local counter fallback.
	 */
	private function get_used(): int {
		$cached = wp_cache_get( 'itwt_line_quota_api', 'itwt' );
		if ( false === $cached ) {
			$cached = get_transient( 'itwt_line_quota_api' );
		}

		if ( false === $cached ) {
			$client = new Line_Client();
			$api    = $client->quota_consumption();
			$used   = $api ? (int) $api['totalUsage'] : (int) get_transient( $this->counter_key() );
			wp_cache_set( 'itwt_line_quota_api', $used, 'itwt', HOUR_IN_SECONDS );
			set_transient( 'itwt_line_quota_api', $used, HOUR_IN_SECONDS );
			return $used;
		}

		return (int) $cached;
	}

	/**
	 * Local monthly counter key.
	 */
	private function counter_key(): string {
		return 'itwt_line_push_' . wp_date( 'Y-m' );
	}
}
