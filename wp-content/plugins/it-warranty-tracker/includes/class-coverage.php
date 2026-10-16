<?php
/**
 * Coverage status calculation from contracts.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Computes warranty / MA coverage status (not stored in DB).
 */
final class Coverage {

	public const STATUS_COVERED       = 'covered';
	public const STATUS_EXPIRING      = 'expiring';
	public const STATUS_EXPIRED       = 'expired';
	public const STATUS_VOID          = 'void';
	public const STATUS_DECOMMISSIONED = 'decommissioned';

	/**
	 * Compute coverage for an asset.
	 *
	 * @param string               $asset_status Asset lifecycle status.
	 * @param array<int,object>    $contracts    Active/cancelled contract rows.
	 * @param int|null             $expiring_days Override settings.
	 * @return array{status:string,days_remaining:int|null,latest_end:string|null,covering_contracts:array}
	 */
	public static function compute( string $asset_status, array $contracts, ?int $expiring_days = null ): array {
		if ( 'void' === $asset_status ) {
			return self::result( self::STATUS_VOID, null, null, array() );
		}
		if ( 'decommissioned' === $asset_status ) {
			return self::result( self::STATUS_DECOMMISSIONED, null, null, array() );
		}

		$today = wp_date( 'Y-m-d' );
		if ( null === $expiring_days ) {
			$settings       = Settings::get();
			$expiring_days  = (int) ( $settings['expiring_days'] ?? 30 );
		}

		$covering   = array();
		$latest_end = null;

		foreach ( $contracts as $contract ) {
			if ( 'active' !== ( $contract->status ?? '' ) ) {
				continue;
			}
			$start = (string) $contract->start_date;
			$end   = (string) $contract->end_date;
			if ( $start <= $today && $end >= $today ) {
				$covering[] = $contract;
			}
			if ( null === $latest_end || $end > $latest_end ) {
				$latest_end = $end;
			}
		}

		if ( empty( $covering ) ) {
			return self::result( self::STATUS_EXPIRED, null, $latest_end, array() );
		}

		$cover_end = null;
		foreach ( $covering as $c ) {
			$end = (string) $c->end_date;
			if ( null === $cover_end || $end > $cover_end ) {
				$cover_end = $end;
			}
		}

		$days = self::days_between( $today, (string) $cover_end );
		if ( $days <= $expiring_days ) {
			return self::result( self::STATUS_EXPIRING, $days, $cover_end, $covering );
		}

		return self::result( self::STATUS_COVERED, $days, $cover_end, $covering );
	}

	/**
	 * Human-readable Thai label for status.
	 *
	 * @param string   $status         Coverage status.
	 * @param int|null $days_remaining Days left.
	 */
	public static function label( string $status, ?int $days_remaining = null ): string {
		switch ( $status ) {
			case self::STATUS_COVERED:
				return null !== $days_remaining
					? sprintf(
						/* translators: %d: days remaining */
						__( 'อยู่ในประกัน — เหลือ %d วัน', 'it-warranty-tracker' ),
						$days_remaining
					)
					: __( 'อยู่ในประกัน', 'it-warranty-tracker' );
			case self::STATUS_EXPIRING:
				return null !== $days_remaining
					? sprintf(
						/* translators: %d: days remaining */
						__( 'เหลือ %d วัน — ใกล้หมดประกัน', 'it-warranty-tracker' ),
						$days_remaining
					)
					: __( 'ใกล้หมดประกัน', 'it-warranty-tracker' );
			case self::STATUS_EXPIRED:
				return __( 'หมดประกัน — ต่อ MA ได้', 'it-warranty-tracker' );
			case self::STATUS_VOID:
				return __( 'โมฆะ', 'it-warranty-tracker' );
			case self::STATUS_DECOMMISSIONED:
				return __( 'เลิกใช้งาน', 'it-warranty-tracker' );
			default:
				return __( 'ไม่ทราบสถานะ', 'it-warranty-tracker' );
		}
	}

	/**
	 * Days between two Y-m-d dates (end - start).
	 *
	 * @param string $from Start date.
	 * @param string $to   End date.
	 */
	public static function days_between( string $from, string $to ): int {
		$a = \DateTimeImmutable::createFromFormat( 'Y-m-d', $from, wp_timezone() );
		$b = \DateTimeImmutable::createFromFormat( 'Y-m-d', $to, wp_timezone() );
		if ( ! $a || ! $b ) {
			return 0;
		}
		return (int) $a->diff( $b )->format( '%r%a' );
	}

	/**
	 * Build result array.
	 *
	 * @param string            $status    Status key.
	 * @param int|null          $days      Days remaining.
	 * @param string|null       $latest    Latest end date.
	 * @param array<int,object> $covering  Covering contracts.
	 * @return array{status:string,days_remaining:int|null,latest_end:string|null,covering_contracts:array}
	 */
	private static function result( string $status, ?int $days, ?string $latest, array $covering ): array {
		return array(
			'status'              => $status,
			'days_remaining'      => $days,
			'latest_end'          => $latest,
			'covering_contracts'  => $covering,
		);
	}
}
