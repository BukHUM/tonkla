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

	public const STATUS_COVERED        = 'covered';
	public const STATUS_EXPIRING       = 'expiring';
	public const STATUS_UPCOMING       = 'upcoming';
	public const STATUS_EXPIRED        = 'expired';
	public const STATUS_VOID           = 'void';
	public const STATUS_DECOMMISSIONED = 'decommissioned';

	/**
	 * Compute coverage for an asset.
	 *
	 * @param string            $asset_status  Asset lifecycle status.
	 * @param array<int,object> $contracts     Active/cancelled contract rows.
	 * @param int|null          $expiring_days Override settings.
	 * @return array{status:string,days_remaining:int|null,latest_end:string|null,next_start:string|null,covering_contracts:array,can_renew:bool}
	 */
	public static function compute( string $asset_status, array $contracts, ?int $expiring_days = null ): array {
		if ( 'void' === $asset_status ) {
			return self::result( self::STATUS_VOID, null, null, null, array(), false );
		}
		if ( 'decommissioned' === $asset_status ) {
			return self::result( self::STATUS_DECOMMISSIONED, null, null, null, array(), false );
		}

		$today = wp_date( 'Y-m-d' );
		if ( null === $expiring_days ) {
			$settings      = Settings::get();
			$expiring_days = (int) ( $settings['expiring_days'] ?? 30 );
		}

		$covering   = array();
		$upcoming   = array();
		$latest_end = null;
		$next_start = null;

		foreach ( $contracts as $contract ) {
			if ( 'active' !== ( $contract->status ?? '' ) ) {
				continue;
			}
			$start = (string) $contract->start_date;
			$end   = (string) $contract->end_date;
			if ( $start <= $today && $end >= $today ) {
				$covering[] = $contract;
			} elseif ( $start > $today && $end >= $start ) {
				$upcoming[] = $contract;
				if ( null === $next_start || $start < $next_start ) {
					$next_start = $start;
				}
			}
			if ( null === $latest_end || $end > $latest_end ) {
				$latest_end = $end;
			}
		}

		if ( ! empty( $covering ) ) {
			$cover_end = null;
			foreach ( $covering as $c ) {
				$end = (string) $c->end_date;
				if ( null === $cover_end || $end > $cover_end ) {
					$cover_end = $end;
				}
			}
			$days = self::days_between( $today, (string) $cover_end );
			if ( $days <= $expiring_days ) {
				return self::result( self::STATUS_EXPIRING, $days, $cover_end, null, $covering, true );
			}
			return self::result( self::STATUS_COVERED, $days, $cover_end, null, $covering, false );
		}

		if ( ! empty( $upcoming ) && null !== $next_start ) {
			$upcoming_end = null;
			foreach ( $upcoming as $c ) {
				if ( (string) $c->start_date !== $next_start ) {
					continue;
				}
				$end = (string) $c->end_date;
				if ( null === $upcoming_end || $end > $upcoming_end ) {
					$upcoming_end = $end;
				}
			}
			return self::result( self::STATUS_UPCOMING, null, $upcoming_end, $next_start, $upcoming, false );
		}

		$can_renew = null !== $latest_end && $latest_end < $today;
		return self::result( self::STATUS_EXPIRED, null, $latest_end, null, array(), $can_renew );
	}

	/**
	 * Whether this asset may still renew MA under configured caps.
	 *
	 * @param object $asset    Asset row (needs status, renew_*, purchase_date).
	 * @param int    $ma_count Existing MA contract count for the asset.
	 */
	public static function is_renewable( object $asset, int $ma_count = 0 ): bool {
		$status = (string) ( $asset->status ?? '' );
		if ( in_array( $status, array( 'void', 'decommissioned' ), true ) ) {
			return false;
		}

		if ( ! empty( $asset->renew_max_times_enabled ) ) {
			$max = (int) ( $asset->renew_max_times ?? 0 );
			if ( $max > 0 && $ma_count >= $max ) {
				return false;
			}
		}

		if ( ! empty( $asset->renew_max_years_enabled ) ) {
			$max_years = (int) ( $asset->renew_max_years ?? 0 );
			$purchase  = (string) ( $asset->purchase_date ?? '' );
			if ( $max_years >= 1 && '' !== $purchase ) {
				$start = \DateTimeImmutable::createFromFormat( '!Y-m-d', $purchase, wp_timezone() );
				if ( $start ) {
					$limit = $start->modify( '+' . $max_years . ' years' )->format( 'Y-m-d' );
					if ( wp_date( 'Y-m-d' ) > $limit ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Human-readable Thai label for status.
	 *
	 * @param string      $status         Coverage status.
	 * @param int|null    $days_remaining Days left.
	 * @param string|null $next_start     Upcoming start Y-m-d.
	 */
	public static function label( string $status, ?int $days_remaining = null, ?string $next_start = null ): string {
		switch ( $status ) {
			case self::STATUS_COVERED:
				return null !== $days_remaining
					? sprintf(
						/* translators: %d: days remaining */
						__( 'อยู่ในประกัน — เหลือ %d วัน', 'tonkla-warranty-tracker' ),
						$days_remaining
					)
					: __( 'อยู่ในประกัน', 'tonkla-warranty-tracker' );
			case self::STATUS_EXPIRING:
				return null !== $days_remaining
					? sprintf(
						/* translators: %d: days remaining */
						__( 'ใกล้หมดประกัน — เหลือ %d วัน', 'tonkla-warranty-tracker' ),
						$days_remaining
					)
					: __( 'ใกล้หมดประกัน', 'tonkla-warranty-tracker' );
			case self::STATUS_UPCOMING:
				if ( $next_start ) {
					return sprintf(
						/* translators: %s: start date */
						__( 'จะเริ่มคุ้มครอง %s', 'tonkla-warranty-tracker' ),
						Dates::format( $next_start )
					);
				}
				return __( 'รอเริ่มคุ้มครอง', 'tonkla-warranty-tracker' );
			case self::STATUS_EXPIRED:
				return __( 'หมดประกันแล้ว', 'tonkla-warranty-tracker' );
			case self::STATUS_VOID:
				return __( 'โมฆะ', 'tonkla-warranty-tracker' );
			case self::STATUS_DECOMMISSIONED:
				return __( 'เลิกใช้งาน', 'tonkla-warranty-tracker' );
			default:
				return __( 'ไม่ทราบสถานะ', 'tonkla-warranty-tracker' );
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
	 * @param string|null       $latest    Coverage / latest end date.
	 * @param string|null       $next      Upcoming start.
	 * @param array<int,object> $covering  Covering / upcoming contracts.
	 * @param bool              $can_renew Suggest MA renew.
	 * @return array{status:string,days_remaining:int|null,latest_end:string|null,next_start:string|null,covering_contracts:array,can_renew:bool}
	 */
	private static function result( string $status, ?int $days, ?string $latest, ?string $next, array $covering, bool $can_renew ): array {
		return array(
			'status'             => $status,
			'days_remaining'     => $days,
			'latest_end'         => $latest,
			'next_start'         => $next,
			'covering_contracts' => $covering,
			'can_renew'          => $can_renew,
		);
	}
}
