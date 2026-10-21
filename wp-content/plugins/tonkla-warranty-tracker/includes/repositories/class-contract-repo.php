<?php
/**
 * Contract repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;

/**
 * CRUD for itwt_contracts with overlap checks.
 */
final class Contract_Repo {

	/**
	 * Find by ID.
	 *
	 * @param int $id Contract ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * List contracts for an asset.
	 *
	 * @param int $asset_id Asset ID.
	 * @return array<int,object>
	 */
	public function for_asset( int $asset_id ): array {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE asset_id = %d ORDER BY start_date ASC LIMIT 200", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Batch-load contracts for many assets (avoid N+1 on print).
	 *
	 * @param array<int,int> $asset_ids Asset IDs.
	 * @return array<int,array<int,object>> Map of asset_id => contracts.
	 */
	public function for_assets( array $asset_ids ): array {
		$asset_ids = array_values( array_unique( array_filter( array_map( 'absint', $asset_ids ) ) ) );
		$map       = array();
		foreach ( $asset_ids as $id ) {
			$map[ $id ] = array();
		}
		if ( empty( $asset_ids ) ) {
			return $map;
		}

		global $wpdb;
		$table  = Installer::table( 'contracts' );
		$limit  = min( 2000, max( 200, count( $asset_ids ) * 20 ) );
		$place  = implode( ',', array_fill( 0, count( $asset_ids ), '%d' ) );
		$params = $asset_ids;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$sql  = "SELECT * FROM {$table} WHERE asset_id IN ({$place}) ORDER BY asset_id ASC, start_date ASC LIMIT {$limit}";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( ! is_array( $rows ) ) {
			return $map;
		}
		foreach ( $rows as $row ) {
			$aid = (int) ( $row->asset_id ?? 0 );
			if ( isset( $map[ $aid ] ) ) {
				$map[ $aid ][] = $row;
			}
		}
		return $map;
	}

	/**
	 * Latest active contract end date per asset (for expiry notifier).
	 *
	 * @param int $asset_id Asset ID.
	 */
	public function latest_active_end( int $asset_id ): ?object {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE asset_id = %d AND status = 'active' ORDER BY end_date DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id
			)
		);
		return $row ?: null;
	}

	/**
	 * Active contract of a given type for an asset.
	 *
	 * @param int    $asset_id Asset ID.
	 * @param string $type     Contract type.
	 */
	public function find_active_by_type( int $asset_id, string $type ): ?object {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE asset_id = %d AND contract_type = %s AND status = 'active'
				ORDER BY end_date DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id,
				sanitize_key( $type )
			)
		);
		return $row ?: null;
	}

	/**
	 * Count contracts of a type (any status).
	 *
	 * @param int    $asset_id Asset ID.
	 * @param string $type     Contract type.
	 */
	public function count_by_type( int $asset_id, string $type ): int {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE asset_id = %d AND contract_type = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id,
				sanitize_key( $type )
			)
		);
	}

	/**
	 * Upsert company warranty contract from asset purchase / warranty end dates.
	 *
	 * @param int         $asset_id Asset ID.
	 * @param string|null $start    Purchase date Y-m-d or null to cancel.
	 * @param string|null $end      Warranty end Y-m-d or null to cancel.
	 * @return int|true|\WP_Error Contract id, true when cancelled, or error.
	 */
	public function sync_company_warranty( int $asset_id, ?string $start, ?string $end ) {
		$existing = $this->find_active_by_type( $asset_id, 'warranty' );

		if ( null === $start || null === $end || '' === $start || '' === $end ) {
			if ( $existing ) {
				$this->cancel( (int) $existing->id );
			}
			return true;
		}

		if ( ! $this->valid_date( $start ) || ! $this->valid_date( $end ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันซื้อ / วันหมดประกันไม่ถูกต้อง', 'tonkla-warranty-tracker' ) );
		}
		if ( $end < $start ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันหมดประกันต้องไม่ก่อนวันซื้อ', 'tonkla-warranty-tracker' ) );
		}

		$data = array(
			'asset_id'      => $asset_id,
			'contract_type' => 'warranty',
			'contract_no'   => $existing ? (string) $existing->contract_no : '',
			'start_date'    => $start,
			'end_date'      => $end,
			'sla_tier'      => $existing ? (string) $existing->sla_tier : '',
			'status'        => 'active',
		);

		if ( $existing ) {
			return $this->save( $data, (int) $existing->id );
		}
		return $this->save( $data, 0 );
	}

	/**
	 * Save contract with overlap validation.
	 *
	 * @param array<string,mixed> $data Contract fields.
	 * @param int                 $id   Existing ID.
	 * @return int|\WP_Error
	 */
	public function save( array $data, int $id = 0 ) {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		$now   = current_time( 'mysql' );

		$start_iso = \ITWT\Dates::parse_input( sanitize_text_field( (string) ( $data['start_date'] ?? '' ) ) );
		$end_iso   = \ITWT\Dates::parse_input( sanitize_text_field( (string) ( $data['end_date'] ?? '' ) ) );

		$row = array(
			'asset_id'      => absint( $data['asset_id'] ?? 0 ),
			'contract_type' => sanitize_key( (string) ( $data['contract_type'] ?? 'warranty' ) ),
			'contract_no'   => sanitize_text_field( (string) ( $data['contract_no'] ?? '' ) ),
			'start_date'    => $start_iso ?? '',
			'end_date'      => $end_iso ?? '',
			'sla_tier'      => sanitize_text_field( (string) ( $data['sla_tier'] ?? '' ) ),
			'status'        => sanitize_key( (string) ( $data['status'] ?? 'active' ) ),
			'updated_at'    => $now,
		);

		if ( ! in_array( $row['contract_type'], array( 'manufacturer', 'warranty', 'ma' ), true ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'ประเภทสัญญาไม่ถูกต้อง', 'tonkla-warranty-tracker' ) );
		}
		if ( ! in_array( $row['status'], array( 'active', 'cancelled' ), true ) ) {
			$row['status'] = 'active';
		}
		if ( $row['asset_id'] < 1 || ! $this->valid_date( $row['start_date'] ) || ! $this->valid_date( $row['end_date'] ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'ข้อมูลสัญญาไม่ครบ', 'tonkla-warranty-tracker' ) );
		}
		if ( $row['end_date'] < $row['start_date'] ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม', 'tonkla-warranty-tracker' ) );
		}

		if ( 'ma' === $row['contract_type'] && 'active' === $row['status'] && 0 === $id ) {
			$cap = $this->validate_ma_renewal_caps( $row['asset_id'], $row['end_date'] );
			if ( is_wp_error( $cap ) ) {
				return $cap;
			}
		}

		if ( 'active' === $row['status'] && $this->has_overlap( $row['asset_id'], $row['contract_type'], $row['start_date'], $row['end_date'], $id ) ) {
			return new \WP_Error( 'itwt_overlap', __( 'ช่วงสัญญานี้ทับกับสัญญาประเภทเดียวกันที่มีอยู่', 'tonkla-warranty-tracker' ) );
		}

		if ( $id > 0 ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			$this->bust_asset_cache( $row['asset_id'] );
			return $id;
		}

		$row['created_by'] = get_current_user_id();
		$row['created_at'] = $now;
		$ok                = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกสัญญาไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
		}
		$this->bust_asset_cache( $row['asset_id'] );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Enforce asset renew caps when inserting MA.
	 *
	 * @param int    $asset_id Asset ID.
	 * @param string $end_date Proposed MA end date.
	 * @return true|\WP_Error
	 */
	public function validate_ma_renewal_caps( int $asset_id, string $end_date ) {
		$asset = ( new Asset_Repo() )->find( $asset_id );
		if ( ! $asset ) {
			return new \WP_Error( 'itwt_missing', __( 'ไม่พบทรัพย์สิน', 'tonkla-warranty-tracker' ) );
		}

		if ( ! empty( $asset->renew_max_times_enabled ) ) {
			$max   = (int) ( $asset->renew_max_times ?? 0 );
			$count = $this->count_by_type( $asset_id, 'ma' );
			if ( $max > 0 && $count >= $max ) {
				return new \WP_Error(
					'itwt_renew_times',
					sprintf(
						/* translators: 1: used count, 2: max times */
						__( 'ต่อ MA ได้สูงสุด %2$d ครั้ง (ใช้ไปแล้ว %1$d)', 'tonkla-warranty-tracker' ),
						$count,
						$max
					)
				);
			}
		}

		if ( ! empty( $asset->renew_max_years_enabled ) ) {
			$max_years = (int) ( $asset->renew_max_years ?? 0 );
			$purchase  = (string) ( $asset->purchase_date ?? '' );
			if ( $max_years < 1 ) {
				return new \WP_Error( 'itwt_renew_years', __( 'ยังไม่ได้ตั้งจำนวนปีสูงสุดในการต่อ MA', 'tonkla-warranty-tracker' ) );
			}
			if ( '' === $purchase || ! $this->valid_date( $purchase ) ) {
				return new \WP_Error( 'itwt_renew_years', __( 'ต้องระบุวันซื้อก่อน จึงจะจำกัดปีที่ต่อ MA ได้', 'tonkla-warranty-tracker' ) );
			}
			$tz    = wp_timezone();
			$start = \DateTimeImmutable::createFromFormat( '!Y-m-d', $purchase, $tz );
			if ( ! $start ) {
				return new \WP_Error( 'itwt_renew_years', __( 'วันซื้อไม่ถูกต้อง', 'tonkla-warranty-tracker' ) );
			}
			$limit = $start->modify( '+' . $max_years . ' years' )->format( 'Y-m-d' );
			if ( $end_date > $limit ) {
				return new \WP_Error(
					'itwt_renew_years',
					sprintf(
						/* translators: 1: max years, 2: limit date */
						__( 'ต่อ MA รวมแล้วได้ไม่เกิน %1$d ปีจากวันซื้อ (ถึง %2$s)', 'tonkla-warranty-tracker' ),
						$max_years,
						\ITWT\Dates::format( $limit )
					)
				);
			}
		}

		return true;
	}

	/**
	 * Cancel a contract.
	 *
	 * @param int $id Contract ID.
	 */
	public function cancel( int $id ): bool {
		$contract = $this->find( $id );
		if ( ! $contract ) {
			return false;
		}
		global $wpdb;
		$wpdb->update(
			Installer::table( 'contracts' ),
			array(
				'status'     => 'cancelled',
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id )
		);
		$this->bust_asset_cache( (int) $contract->asset_id );
		return true;
	}

	/**
	 * Permanently delete a contract row.
	 *
	 * @param int $id Contract ID.
	 * @return true|\WP_Error
	 */
	public function delete( int $id ) {
		$contract = $this->find( $id );
		if ( ! $contract ) {
			return new \WP_Error( 'itwt_missing', __( 'ไม่พบสัญญา', 'tonkla-warranty-tracker' ) );
		}
		global $wpdb;
		$ok = $wpdb->delete( Installer::table( 'contracts' ), array( 'id' => $id ), array( '%d' ) );
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'ลบสัญญาไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
		}
		$this->bust_asset_cache( (int) $contract->asset_id );
		return true;
	}

	/**
	 * Contracts matching a milestone day for expiry notifier.
	 *
	 * @param int $milestone Days until end.
	 * @param int $limit     Batch size.
	 * @return array<int,object>
	 */
	public function for_milestone( int $milestone, int $limit = 200 ): array {
		global $wpdb;
		$table   = Installer::table( 'contracts' );
		$target  = wp_date( 'Y-m-d', strtotime( '+' . $milestone . ' days' ) );
		$limit   = min( 200, max( 1, $limit ) );

		// Only the latest active contract per asset whose end_date matches target.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c.*
				FROM {$table} c
				INNER JOIN (
					SELECT asset_id, MAX(end_date) AS max_end
					FROM {$table}
					WHERE status = 'active'
					GROUP BY asset_id
				) latest ON latest.asset_id = c.asset_id AND latest.max_end = c.end_date
				WHERE c.status = 'active' AND c.end_date = %s
				LIMIT %d",
				$target,
				$limit
			)
		);
		// phpcs:enable

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Check overlapping active contracts of same type.
	 *
	 * @param int    $asset_id Asset ID.
	 * @param string $type     Contract type.
	 * @param string $start    Start date.
	 * @param string $end      End date.
	 * @param int    $exclude  Exclude contract ID.
	 */
	private function has_overlap( int $asset_id, string $type, string $start, string $end, int $exclude = 0 ): bool {
		global $wpdb;
		$table = Installer::table( 'contracts' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE asset_id = %d AND contract_type = %s AND status = 'active'
				AND id <> %d
				AND start_date <= %s AND end_date >= %s",
				$asset_id,
				$type,
				$exclude,
				$end,
				$start
			)
		);
		// phpcs:enable
		return $count > 0;
	}

	/**
	 * Validate date (ISO Y-m-d or dd/mm/yyyy).
	 *
	 * @param string $date Date string.
	 */
	private function valid_date( string $date ): bool {
		return null !== \ITWT\Dates::parse_input( $date );
	}

	/**
	 * Bust lookup cache for asset.
	 *
	 * @param int $asset_id Asset ID.
	 */
	private function bust_asset_cache( int $asset_id ): void {
		$repo  = new Asset_Repo();
		$asset = $repo->find( $asset_id );
		if ( $asset ) {
			$repo->bust_cache( (string) $asset->service_tag );
		}
	}
}
