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

		$row = array(
			'asset_id'      => absint( $data['asset_id'] ?? 0 ),
			'contract_type' => sanitize_key( (string) ( $data['contract_type'] ?? 'warranty' ) ),
			'contract_no'   => sanitize_text_field( (string) ( $data['contract_no'] ?? '' ) ),
			'start_date'    => sanitize_text_field( (string) ( $data['start_date'] ?? '' ) ),
			'end_date'      => sanitize_text_field( (string) ( $data['end_date'] ?? '' ) ),
			'sla_tier'      => sanitize_text_field( (string) ( $data['sla_tier'] ?? '' ) ),
			'status'        => sanitize_key( (string) ( $data['status'] ?? 'active' ) ),
			'updated_at'    => $now,
		);

		if ( ! in_array( $row['contract_type'], array( 'manufacturer', 'warranty', 'ma' ), true ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'ประเภทสัญญาไม่ถูกต้อง', 'it-warranty-tracker' ) );
		}
		if ( ! in_array( $row['status'], array( 'active', 'cancelled' ), true ) ) {
			$row['status'] = 'active';
		}
		if ( $row['asset_id'] < 1 || ! $this->valid_date( $row['start_date'] ) || ! $this->valid_date( $row['end_date'] ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'ข้อมูลสัญญาไม่ครบ', 'it-warranty-tracker' ) );
		}
		if ( $row['end_date'] < $row['start_date'] ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม', 'it-warranty-tracker' ) );
		}

		if ( 'active' === $row['status'] && $this->has_overlap( $row['asset_id'], $row['contract_type'], $row['start_date'], $row['end_date'], $id ) ) {
			return new \WP_Error( 'itwt_overlap', __( 'ช่วงสัญญานี้ทับกับสัญญาประเภทเดียวกันที่มีอยู่', 'it-warranty-tracker' ) );
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
			return new \WP_Error( 'itwt_db', __( 'บันทึกสัญญาไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		$this->bust_asset_cache( $row['asset_id'] );
		return (int) $wpdb->insert_id;
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
	 * Validate Y-m-d date.
	 *
	 * @param string $date Date string.
	 */
	private function valid_date( string $date ): bool {
		$d = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
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
