<?php
/**
 * Customer / partner repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;
use ITWT\Installer;
use ITWT\Labels;

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
	 * Find by external import code.
	 *
	 * @param string $code External code.
	 */
	public function find_by_external_code( string $code ): ?object {
		$code = sanitize_text_field( $code );
		if ( '' === $code ) {
			return null;
		}
		global $wpdb;
		$table = Installer::table( 'customers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE external_code = %s LIMIT 1", $code ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Find by tax ID.
	 *
	 * @param string $tax_id Digits-only tax ID.
	 */
	public function find_by_tax_id( string $tax_id ): ?object {
		$tax_id = self::normalize_tax_id( $tax_id );
		if ( '' === $tax_id ) {
			return null;
		}
		global $wpdb;
		$table = Installer::table( 'customers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE tax_id = %s LIMIT 1", $tax_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Find by exact company name.
	 *
	 * @param string $name Company / person name.
	 */
	public function find_by_company_name( string $name ): ?object {
		$name = sanitize_text_field( $name );
		if ( '' === $name ) {
			return null;
		}
		global $wpdb;
		$table = Installer::table( 'customers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE company_name = %s LIMIT 1", $name ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Resolve existing row for import upsert.
	 *
	 * @param array<string,mixed> $data Import payload.
	 */
	public function find_import_match( array $data ): ?object {
		$external = sanitize_text_field( (string) ( $data['external_code'] ?? '' ) );
		if ( '' !== $external ) {
			$found = $this->find_by_external_code( $external );
			if ( $found ) {
				return $found;
			}
		}
		$tax = self::normalize_tax_id( (string) ( $data['tax_id'] ?? '' ) );
		if ( '' !== $tax ) {
			$found = $this->find_by_tax_id( $tax );
			if ( $found ) {
				return $found;
			}
		}
		return $this->find_by_company_name( (string) ( $data['company_name'] ?? '' ) );
	}

	/**
	 * Generate a unique code e.g. CUS-2026-A3K9 or PTR-2026-A3K9.
	 *
	 * @param string $party_role Party role key.
	 */
	public function generate_code( string $party_role = 'customer' ): string {
		$prefix   = ( 'partner' === $party_role ) ? 'PTR' : 'CUS';
		$alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$year     = wp_date( 'Y' );

		for ( $attempt = 0; $attempt < 20; $attempt++ ) {
			$suffix = '';
			for ( $i = 0; $i < 4; $i++ ) {
				$suffix .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
			}
			$code = sprintf( '%s-%s-%s', $prefix, $year, $suffix );
			if ( null === $this->find_by_code( $code ) ) {
				return $code;
			}
		}

		return $prefix . '-' . $year . '-' . strtoupper( substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 6 ) );
	}

	/**
	 * Normalize tax / national ID to digits only.
	 *
	 * @param string $raw Raw input.
	 */
	public static function normalize_tax_id( string $raw ): string {
		return preg_replace( '/\D+/', '', $raw ) ?? '';
	}

	/**
	 * Allowed page sizes for the admin list.
	 *
	 * @return array<int,int>
	 */
	public static function per_page_options(): array {
		return Admin_List::per_page_options();
	}

	/**
	 * Clamp per_page to an allowed option.
	 *
	 * @param int $per_page Requested size.
	 */
	public static function normalize_per_page( int $per_page ): int {
		return Admin_List::normalize_per_page( $per_page );
	}

	/**
	 * Paginated list with search / role filter.
	 *
	 * @param array{search?:string,party_role?:string,page?:int,per_page?:int} $args Query args.
	 * @return array{items:array<int,object>,total:int}
	 */
	public function list( array $args = array() ): array {
		global $wpdb;
		$table      = Installer::table( 'customers' );
		$search     = isset( $args['search'] ) ? sanitize_text_field( (string) $args['search'] ) : '';
		$party_role = isset( $args['party_role'] ) ? sanitize_key( (string) $args['party_role'] ) : '';
		$page       = max( 1, (int) ( $args['page'] ?? 1 ) );
		$per_page   = self::normalize_per_page( (int) ( $args['per_page'] ?? 20 ) );
		$offset     = ( $page - 1 ) * $per_page;

		$where  = '1=1';
		$params = array();

		if ( '' !== $party_role && array_key_exists( $party_role, Labels::party_roles() ) ) {
			if ( 'customer' === $party_role ) {
				$where .= " AND party_role IN ('customer','both')";
			} elseif ( 'partner' === $party_role ) {
				$where .= " AND party_role IN ('partner','both')";
			} else {
				$where   .= ' AND party_role = %s';
				$params[] = $party_role;
			}
		}

		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where   .= ' AND (company_name LIKE %s OR customer_code LIKE %s OR contact_name LIKE %s OR tax_id LIKE %s OR external_code LIKE %s)';
			$params[] = $like;
			$params[] = $like;
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
	 * Insert or update customer / partner.
	 *
	 * @param array<string,mixed> $data Fields.
	 * @param int                 $id   Existing ID or 0.
	 * @return int|\WP_Error
	 */
	public function save( array $data, int $id = 0 ) {
		global $wpdb;
		$table = Installer::table( 'customers' );
		$now   = current_time( 'mysql' );

		$role = sanitize_key( (string) ( $data['party_role'] ?? 'customer' ) );
		if ( ! array_key_exists( $role, Labels::party_roles() ) ) {
			$role = 'customer';
		}

		$code = sanitize_text_field( (string) ( $data['customer_code'] ?? '' ) );
		if ( '' === $code ) {
			if ( $id > 0 ) {
				$existing = $this->find( $id );
				$code     = $existing ? (string) $existing->customer_code : $this->generate_code( $role );
			} else {
				$code = $this->generate_code( $role );
			}
		}

		$type = sanitize_key( (string) ( $data['customer_type'] ?? 'juristic' ) );
		if ( ! array_key_exists( $type, Labels::customer_types() ) ) {
			$type = 'juristic';
		}

		$location = sanitize_key( (string) ( $data['business_location'] ?? 'thai' ) );
		if ( ! array_key_exists( $location, Labels::business_locations() ) ) {
			$location = 'thai';
		}

		$tax_id = self::normalize_tax_id( (string) ( $data['tax_id'] ?? '' ) );
		if ( strlen( $tax_id ) > 20 ) {
			$tax_id = substr( $tax_id, 0, 20 );
		}

		$mobile = sanitize_text_field( (string) ( $data['mobile'] ?? '' ) );
		$office = sanitize_text_field( (string) ( $data['office_phone'] ?? '' ) );
		$phone  = sanitize_text_field( (string) ( $data['phone'] ?? '' ) );
		if ( '' === $phone ) {
			$phone = '' !== $mobile ? $mobile : $office;
		}

		$row = array(
			'customer_code'     => $code,
			'company_name'      => sanitize_text_field( (string) ( $data['company_name'] ?? '' ) ),
			'customer_type'     => $type,
			'tax_id'            => $tax_id,
			'party_role'        => $role,
			'external_code'     => sanitize_text_field( (string) ( $data['external_code'] ?? '' ) ),
			'business_location' => $location,
			'address'           => sanitize_textarea_field( (string) ( $data['address'] ?? '' ) ),
			'address_2'         => sanitize_text_field( (string) ( $data['address_2'] ?? '' ) ),
			'address_3'         => sanitize_text_field( (string) ( $data['address_3'] ?? '' ) ),
			'postal_code'       => sanitize_text_field( (string) ( $data['postal_code'] ?? '' ) ),
			'branch_code'       => sanitize_text_field( (string) ( $data['branch_code'] ?? '' ) ),
			'branch_name'       => sanitize_text_field( (string) ( $data['branch_name'] ?? '' ) ),
			'contact_name'      => sanitize_text_field( (string) ( $data['contact_name'] ?? '' ) ),
			'phone'             => $phone,
			'mobile'            => $mobile,
			'office_phone'      => $office,
			'fax'               => sanitize_text_field( (string) ( $data['fax'] ?? '' ) ),
			'email'             => sanitize_email( (string) ( $data['email'] ?? '' ) ),
			'credit_days'       => max( 0, (int) ( $data['credit_days'] ?? 0 ) ),
			'bank_name'         => sanitize_text_field( (string) ( $data['bank_name'] ?? '' ) ),
			'bank_account'      => sanitize_text_field( (string) ( $data['bank_account'] ?? '' ) ),
			'bank_account_name' => sanitize_text_field( (string) ( $data['bank_account_name'] ?? '' ) ),
			'bank_account_type' => sanitize_text_field( (string) ( $data['bank_account_type'] ?? '' ) ),
			'bank_branch'       => sanitize_text_field( (string) ( $data['bank_branch'] ?? '' ) ),
			'bank_branch_code'  => sanitize_text_field( (string) ( $data['bank_branch_code'] ?? '' ) ),
			'updated_at'        => $now,
		);

		if ( '' === $row['company_name'] ) {
			return new \WP_Error( 'itwt_invalid', __( 'กรุณาระบุชื่อลูกค้า / ชื่อบริษัท', 'tonkla-warranty-tracker' ) );
		}

		$dup = $this->find_by_code( $row['customer_code'] );
		if ( $dup && (int) $dup->id !== $id ) {
			return new \WP_Error( 'itwt_duplicate', __( 'รหัสลูกค้าซ้ำในระบบ', 'tonkla-warranty-tracker' ) );
		}

		if ( $id > 0 ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			return $id;
		}

		$row['created_at'] = $now;
		$ok                = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			Installer::migrate_customer_columns();
			$ok = $wpdb->insert( $table, $row );
		}
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกลูกค้าไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
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
			return new \WP_Error( 'itwt_has_assets', __( 'ลบลูกค้าไม่ได้เพราะยังมีทรัพย์สินผูกอยู่', 'tonkla-warranty-tracker' ) );
		}
		$table = Installer::table( 'customers' );
		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		return true;
	}

	/**
	 * Bulk delete; skips rows that still have assets.
	 *
	 * @param array<int,int> $ids Customer IDs.
	 * @return array{deleted:int,skipped:int}
	 */
	public function delete_many( array $ids ): array {
		$deleted = 0;
		$skipped = 0;
		foreach ( array_unique( array_map( 'absint', $ids ) ) as $id ) {
			if ( $id <= 0 ) {
				continue;
			}
			$result = $this->delete( $id );
			if ( is_wp_error( $result ) ) {
				++$skipped;
			} else {
				++$deleted;
			}
		}
		return array(
			'deleted' => $deleted,
			'skipped' => $skipped,
		);
	}

	/**
	 * Bulk update party role.
	 *
	 * @param array<int,int> $ids  Customer IDs.
	 * @param string         $role Party role key.
	 * @return int|\WP_Error Updated count.
	 */
	public function update_party_role_many( array $ids, string $role ) {
		$role = sanitize_key( $role );
		if ( ! array_key_exists( $role, Labels::party_roles() ) ) {
			return new \WP_Error( 'itwt_role', __( 'บทบาทไม่ถูกต้อง', 'tonkla-warranty-tracker' ) );
		}
		$ids = array_values( array_filter( array_unique( array_map( 'absint', $ids ) ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}
		global $wpdb;
		$table   = Installer::table( 'customers' );
		$now     = current_time( 'mysql' );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = "UPDATE {$table} SET party_role = %s, updated_at = %s WHERE id IN ({$placeholders})";
		$params       = array_merge( array( $role, $now ), $ids );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $wpdb->prepare( $sql, ...$params ) );
		return count( $ids );
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
