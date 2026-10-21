<?php
/**
 * Service log repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;

/**
 * CRUD for itwt_service_logs.
 */
final class Service_Log_Repo {

	/**
	 * Find by ID.
	 *
	 * @param int $id Log ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'service_logs' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Logs for an asset.
	 *
	 * @param int  $asset_id Asset ID.
	 * @param bool $public_only Only rows with public_summary.
	 * @return array<int,object>
	 */
	public function for_asset( int $asset_id, bool $public_only = false ): array {
		global $wpdb;
		$table = Installer::table( 'service_logs' );
		$sql   = "SELECT * FROM {$table} WHERE asset_id = %d";
		if ( $public_only ) {
			$sql .= ' AND public_summary IS NOT NULL AND public_summary <> \'\'';
		}
		$sql .= ' ORDER BY service_date DESC LIMIT 100';

		$rows = $wpdb->get_results(
			$wpdb->prepare( $sql, $asset_id ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Save log.
	 *
	 * @param array<string,mixed> $data Log fields.
	 * @param int                 $id   Existing ID.
	 * @return int|\WP_Error
	 */
	public function save( array $data, int $id = 0 ) {
		global $wpdb;
		$table = Installer::table( 'service_logs' );
		$now   = current_time( 'mysql' );

		$summary = isset( $data['public_summary'] ) ? sanitize_text_field( (string) $data['public_summary'] ) : '';
		if ( '' === $summary ) {
			$summary = null;
		}

		$service_iso = \ITWT\Dates::parse_input(
			sanitize_text_field( (string) ( $data['service_date'] ?? wp_date( 'Y-m-d' ) ) )
		);
		if ( null === $service_iso ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันที่บริการไม่ถูกต้อง (ใช้ วว/ดด/ปปปป)', 'tonkla-warranty-tracker' ) );
		}

		$row = array(
			'asset_id'       => absint( $data['asset_id'] ?? 0 ),
			'service_date'   => $service_iso,
			'service_type'   => sanitize_key( (string) ( $data['service_type'] ?? 'claim' ) ),
			'symptom'        => sanitize_textarea_field( (string) ( $data['symptom'] ?? '' ) ),
			'resolution'     => sanitize_textarea_field( (string) ( $data['resolution'] ?? '' ) ),
			'public_summary' => $summary,
			'technician_id'  => absint( $data['technician_id'] ?? get_current_user_id() ),
			'updated_at'     => $now,
		);

		if ( ! in_array( $row['service_type'], array( 'claim', 'pm', 'upgrade', 'incident' ), true ) ) {
			$row['service_type'] = 'claim';
		}
		if ( $row['asset_id'] < 1 ) {
			return new \WP_Error( 'itwt_invalid', __( 'ต้องระบุทรัพย์สิน', 'tonkla-warranty-tracker' ) );
		}

		if ( $id > 0 ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			$this->bust_cache( $row['asset_id'] );
			return $id;
		}

		$row['created_at'] = $now;
		$ok                = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกประวัติบริการไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
		}
		$this->bust_cache( $row['asset_id'] );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Delete log.
	 *
	 * @param int $id Log ID.
	 */
	public function delete( int $id ): bool {
		$log = $this->find( $id );
		if ( ! $log ) {
			return false;
		}
		global $wpdb;
		$wpdb->delete( Installer::table( 'service_logs' ), array( 'id' => $id ), array( '%d' ) );
		$this->bust_cache( (int) $log->asset_id );
		return true;
	}

	/**
	 * Bust asset lookup cache.
	 *
	 * @param int $asset_id Asset ID.
	 */
	private function bust_cache( int $asset_id ): void {
		$repo  = new Asset_Repo();
		$asset = $repo->find( $asset_id );
		if ( $asset ) {
			$repo->bust_cache( (string) $asset->service_tag );
		}
	}
}
