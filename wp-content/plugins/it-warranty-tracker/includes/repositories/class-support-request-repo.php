<?php
/**
 * Support request repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;
use ITWT\Settings;

/**
 * CRUD for itwt_support_requests.
 */
final class Support_Request_Repo {

	/**
	 * Find by ID.
	 *
	 * @param int $id Request ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'support_requests' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Paginated list.
	 *
	 * @param array<string,mixed> $args Filters.
	 * @return array{items:array<int,object>,total:int}
	 */
	public function list( array $args = array() ): array {
		global $wpdb;
		$table    = Installer::table( 'support_requests' );
		$status   = isset( $args['status'] ) ? sanitize_key( (string) $args['status'] ) : '';
		$page     = max( 1, (int) ( $args['page'] ?? 1 ) );
		$per_page = min( 50, max( 1, (int) ( $args['per_page'] ?? 20 ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$where  = '1=1';
		$params = array();
		if ( '' !== $status ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where}", ...$params ) );
			$params[] = $per_page;
			$params[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d", ...$params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
		}

		return array(
			'items' => is_array( $items ) ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Count by status.
	 *
	 * @param string $status Status key.
	 */
	public function count_by_status( string $status ): int {
		global $wpdb;
		$table = Installer::table( 'support_requests' );
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", $status ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Create support request with dedupe.
	 *
	 * @param array<string,mixed> $data Request fields.
	 * @return int|\WP_Error
	 */
	public function create( array $data ) {
		global $wpdb;
		$table = Installer::table( 'support_requests' );
		$now   = current_time( 'mysql' );
		$today = wp_date( 'Y-m-d' );

		$asset_id = absint( $data['asset_id'] ?? 0 );
		$phone    = sanitize_text_field( (string) ( $data['phone'] ?? '' ) );
		$detail   = sanitize_textarea_field( (string) ( $data['detail'] ?? '' ) );
		$type     = sanitize_key( (string) ( $data['request_type'] ?? 'repair' ) );

		if ( ! in_array( $type, array( 'repair', 'renew_ma' ), true ) ) {
			$type = 'repair';
		}
		if ( $asset_id < 1 || '' === $phone || '' === sanitize_text_field( (string) ( $data['contact_name'] ?? '' ) ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'กรุณากรอกข้อมูลให้ครบ', 'it-warranty-tracker' ) );
		}

		$hash = hash( 'sha256', $asset_id . '|' . $phone . '|' . $detail . '|' . $today );

		$existing = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE dedupe_hash = %s LIMIT 1", $hash ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		if ( $existing ) {
			return new \WP_Error( 'itwt_duplicate', __( 'คำขอนี้ถูกส่งไปแล้ววันนี้', 'it-warranty-tracker' ), array( 'status' => 409 ) );
		}

		$row = array(
			'asset_id'      => $asset_id,
			'request_type'  => $type,
			'contact_name'  => sanitize_text_field( (string) $data['contact_name'] ),
			'phone'         => $phone,
			'detail'        => $detail,
			'status'        => 'new',
			'dedupe_hash'   => $hash,
			'consent_at'    => $now,
			'created_at'    => $now,
			'updated_at'    => $now,
		);

		$ok = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกคำขอไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Update status.
	 *
	 * @param int    $id     Request ID.
	 * @param string $status New status.
	 */
	public function update_status( int $id, string $status ): bool {
		if ( ! in_array( $status, array( 'new', 'in_progress', 'done', 'spam' ), true ) ) {
			return false;
		}
		global $wpdb;
		$wpdb->update(
			Installer::table( 'support_requests' ),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id )
		);
		return true;
	}

	/**
	 * Delete requests older than retention months.
	 *
	 * @return int Rows deleted.
	 */
	public function purge_expired(): int {
		global $wpdb;
		$settings = Settings::get();
		$months   = max( 1, (int) ( $settings['request_retention_months'] ?? 24 ) );
		$table    = Installer::table( 'support_requests' );
		$cutoff   = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) );

		// Use site timezone via current_time math.
		$cutoff_local = wp_date( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s LIMIT 500", $cutoff_local )
		);

		unset( $cutoff );
		return is_int( $deleted ) ? $deleted : 0;
	}
}
