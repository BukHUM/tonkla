<?php
/**
 * Customer repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;

/**
 * CRUD for itwt_customers.
 */
final class Customer_Repo {

	/**
	 * Find by ID.
	 *
	 * @param int $id Customer ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'customers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Find by customer code.
	 *
	 * @param string $code Customer code.
	 */
	public function find_by_code( string $code ): ?object {
		global $wpdb;
		$table = Installer::table( 'customers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE customer_code = %s LIMIT 1", $code ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Paginated list with search.
	 *
	 * @param array{search?:string,page?:int,per_page?:int} $args Query args.
	 * @return array{items:array<int,object>,total:int}
	 */
	public function list( array $args = array() ): array {
		global $wpdb;
		$table    = Installer::table( 'customers' );
		$search   = isset( $args['search'] ) ? sanitize_text_field( (string) $args['search'] ) : '';
		$page     = max( 1, (int) ( $args['page'] ?? 1 ) );
		$per_page = min( 50, max( 1, (int) ( $args['per_page'] ?? 20 ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$where  = '1=1';
		$params = array();
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where   .= ' AND (company_name LIKE %s OR customer_code LIKE %s OR contact_name LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		$list_sql  = "SELECT * FROM {$table} WHERE {$where} ORDER BY company_name ASC LIMIT %d OFFSET %d";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) );
			$params[] = $per_page;
			$params[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( $list_sql, ...$params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY company_name ASC LIMIT %d OFFSET %d", $per_page, $offset ) );
		}

		return array(
			'items' => is_array( $items ) ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Insert or update customer.
	 *
	 * @param array<string,mixed> $data Customer fields.
	 * @param int                 $id   Existing ID or 0.
	 * @return int|\WP_Error
	 */
	public function save( array $data, int $id = 0 ) {
		global $wpdb;
		$table = Installer::table( 'customers' );
		$now   = current_time( 'mysql' );

		$row = array(
			'customer_code' => sanitize_text_field( (string) ( $data['customer_code'] ?? '' ) ),
			'company_name'  => sanitize_text_field( (string) ( $data['company_name'] ?? '' ) ),
			'contact_name'  => sanitize_text_field( (string) ( $data['contact_name'] ?? '' ) ),
			'phone'         => sanitize_text_field( (string) ( $data['phone'] ?? '' ) ),
			'email'         => sanitize_email( (string) ( $data['email'] ?? '' ) ),
			'updated_at'    => $now,
		);

		if ( '' === $row['customer_code'] || '' === $row['company_name'] ) {
			return new \WP_Error( 'itwt_invalid', __( 'รหัสลูกค้าและชื่อบริษัทจำเป็น', 'it-warranty-tracker' ) );
		}

		if ( $id > 0 ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			return $id;
		}

		$row['created_at'] = $now;
		$ok                = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกลูกค้าไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Delete customer if no assets attached.
	 *
	 * @param int $id Customer ID.
	 * @return true|\WP_Error
	 */
	public function delete( int $id ) {
		global $wpdb;
		$assets = Installer::table( 'assets' );
		$count  = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$assets} WHERE customer_id = %d", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		if ( $count > 0 ) {
			return new \WP_Error( 'itwt_has_assets', __( 'ลบลูกค้าไม่ได้เพราะยังมีทรัพย์สินผูกอยู่', 'it-warranty-tracker' ) );
		}
		$table = Installer::table( 'customers' );
		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		return true;
	}

	/**
	 * Count assets for a customer.
	 *
	 * @param int $id Customer ID.
	 */
	public function asset_count( int $id ): int {
		global $wpdb;
		$assets = Installer::table( 'assets' );
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$assets} WHERE customer_id = %d", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
