<?php
/**
 * Asset repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Coverage;
use ITWT\Installer;
use ITWT\Service_Tag;

/**
 * CRUD and lookup for itwt_assets.
 */
final class Asset_Repo {

	/**
	 * Find by ID.
	 *
	 * @param int $id Asset ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'assets' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Find multiple assets by ID (keyed by id).
	 *
	 * @param array<int,int> $ids Asset IDs.
	 * @return array<int,object>
	 */
	public function find_many( array $ids ): array {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}
		$ids = array_slice( $ids, 0, 100 );

		global $wpdb;
		$table       = Installer::table( 'assets' );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id IN ({$placeholders})", ...$ids )
		);

		$map = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$map[ (int) $row->id ] = $row;
			}
		}
		return $map;
	}

	/**
	 * Find by service tag (normalized).
	 *
	 * @param string $tag Service tag.
	 */
	public function find_by_tag( string $tag ): ?object {
		global $wpdb;
		$table = Installer::table( 'assets' );
		$tag   = Service_Tag::normalize( $tag );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE service_tag = %s LIMIT 1", $tag ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Find by exact serial (may return multiple brands).
	 *
	 * @param string $serial Serial number.
	 * @return array<int,object>
	 */
	public function find_by_serial( string $serial ): array {
		global $wpdb;
		$table  = Installer::table( 'assets' );
		$serial = Service_Tag::normalize( $serial );
		$rows   = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE serial_number = %s LIMIT 20", $serial ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Public lookup by tag or serial.
	 *
	 * @param string $query Search query.
	 * @return array{type:string,assets:array<int,object>}
	 */
	public function lookup( string $query ): array {
		$q = Service_Tag::normalize( $query );
		if ( '' === $q ) {
			return array(
				'type'   => 'none',
				'assets' => array(),
			);
		}

		$by_tag = $this->find_by_tag( $q );
		if ( $by_tag ) {
			return array(
				'type'   => 'single',
				'assets' => array( $by_tag ),
			);
		}

		$by_serial = $this->find_by_serial( $q );
		if ( count( $by_serial ) === 1 ) {
			return array(
				'type'   => 'single',
				'assets' => $by_serial,
			);
		}
		if ( count( $by_serial ) > 1 ) {
			return array(
				'type'   => 'choices',
				'assets' => $by_serial,
			);
		}

		return array(
			'type'   => 'none',
			'assets' => array(),
		);
	}

	/**
	 * Paginated admin list.
	 *
	 * @param array<string,mixed> $args Filters.
	 * @return array{items:array<int,object>,total:int}
	 */
	public function list( array $args = array() ): array {
		global $wpdb;
		$assets    = Installer::table( 'assets' );
		$contracts = Installer::table( 'contracts' );
		$search    = isset( $args['search'] ) ? sanitize_text_field( (string) $args['search'] ) : '';
		$asset_type = isset( $args['asset_type'] ) ? sanitize_key( (string) $args['asset_type'] ) : '';
		$coverage   = isset( $args['coverage'] ) ? sanitize_key( (string) $args['coverage'] ) : '';
		$page       = max( 1, (int) ( $args['page'] ?? 1 ) );
		$per_page   = \ITWT\Admin_List::normalize_per_page( (int) ( $args['per_page'] ?? 20 ) );
		$offset     = ( $page - 1 ) * $per_page;
		$today      = wp_date( 'Y-m-d' );
		$settings   = \ITWT\Settings::get();
		$expiring   = (int) ( $settings['expiring_days'] ?? 30 );

		$where  = '1=1';
		$params = array();

		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where   .= ' AND (a.service_tag LIKE %s OR a.serial_number LIKE %s OR a.model_name LIKE %s OR a.brand LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( '' !== $asset_type ) {
			$where   .= ' AND a.asset_type = %s';
			$params[] = $asset_type;
		}

		if ( 'void' === $coverage ) {
			$where .= " AND a.status = 'void'";
		} elseif ( 'decommissioned' === $coverage ) {
			$where .= " AND a.status = 'decommissioned'";
		} elseif ( in_array( $coverage, array( 'covered', 'expiring', 'expired' ), true ) ) {
			$where .= " AND a.status = 'active'";
			if ( 'covered' === $coverage ) {
				$where   .= ' AND EXISTS (SELECT 1 FROM ' . $contracts . ' c WHERE c.asset_id = a.id AND c.status = %s AND c.start_date <= %s AND c.end_date >= %s AND c.end_date > %s)';
				$params[] = 'active';
				$params[] = $today;
				$params[] = $today;
				$params[] = wp_date( 'Y-m-d', strtotime( '+' . $expiring . ' days' ) );
			} elseif ( 'expiring' === $coverage ) {
				$where   .= ' AND EXISTS (SELECT 1 FROM ' . $contracts . ' c WHERE c.asset_id = a.id AND c.status = %s AND c.start_date <= %s AND c.end_date >= %s AND c.end_date <= %s)';
				$params[] = 'active';
				$params[] = $today;
				$params[] = $today;
				$params[] = wp_date( 'Y-m-d', strtotime( '+' . $expiring . ' days' ) );
			} else {
				$where   .= ' AND NOT EXISTS (SELECT 1 FROM ' . $contracts . ' c WHERE c.asset_id = a.id AND c.status = %s AND c.start_date <= %s AND c.end_date >= %s)';
				$params[] = 'active';
				$params[] = $today;
				$params[] = $today;
			}
		}

		$count_sql = "SELECT COUNT(*) FROM {$assets} a WHERE {$where}";
		$list_sql  = "SELECT a.* FROM {$assets} a WHERE {$where} ORDER BY a.updated_at DESC LIMIT %d OFFSET %d";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) );
			$params[] = $per_page;
			$params[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( $list_sql, ...$params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$assets} a WHERE {$where}" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT a.* FROM {$assets} a WHERE {$where} ORDER BY a.updated_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
		}

		return array(
			'items' => is_array( $items ) ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Save asset (insert/update).
	 *
	 * @param array<string,mixed> $data Asset fields.
	 * @param int                 $id   Existing ID.
	 * @return int|\WP_Error
	 */
	public function save( array $data, int $id = 0 ) {
		global $wpdb;
		$table = Installer::table( 'assets' );
		$now   = current_time( 'mysql' );
		$user  = get_current_user_id();

		$service_tag = Service_Tag::normalize( (string) ( $data['service_tag'] ?? '' ) );
		if ( '' === $service_tag ) {
			$service_tag = Service_Tag::generate(
				function ( string $tag ): bool {
					return null !== $this->find_by_tag( $tag );
				}
			);
			if ( null === $service_tag ) {
				return new \WP_Error( 'itwt_tag', __( 'สร้าง Service Tag ไม่สำเร็จ', 'it-warranty-tracker' ) );
			}
		}

		$serial = isset( $data['serial_number'] ) && '' !== (string) $data['serial_number']
			? Service_Tag::normalize( (string) $data['serial_number'] )
			: null;

		$specs = $data['specs_json'] ?? '[]';
		if ( is_array( $specs ) ) {
			$specs = wp_json_encode( $specs );
		}

		$default_type = \ITWT\Asset_Types::default_slug();
		$asset_type   = sanitize_key( (string) ( $data['asset_type'] ?? $default_type ) );
		if ( ! \ITWT\Asset_Types::is_valid( $asset_type ) ) {
			$asset_type = $default_type;
		}

		$purchase = $this->nullable_date( (string) ( $data['purchase_date'] ?? '' ) );
		$warranty = $this->nullable_date( (string) ( $data['warranty_end_date'] ?? '' ) );
		if ( is_wp_error( $purchase ) ) {
			return $purchase;
		}
		if ( is_wp_error( $warranty ) ) {
			return $warranty;
		}
		if ( null !== $purchase && null !== $warranty && $warranty < $purchase ) {
			return new \WP_Error( 'itwt_invalid', __( 'วันหมดประกันต้องไม่ก่อนวันซื้อ', 'it-warranty-tracker' ) );
		}

		$years_on  = ! empty( $data['renew_max_years_enabled'] );
		$times_on  = ! empty( $data['renew_max_times_enabled'] );
		$years_val = isset( $data['renew_max_years'] ) ? absint( $data['renew_max_years'] ) : 0;
		$times_val = isset( $data['renew_max_times'] ) ? absint( $data['renew_max_times'] ) : 0;
		if ( $years_on && ( $years_val < 1 || $years_val > 50 ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'จำนวนปีที่ต่อได้ต้องอยู่ระหว่าง 1–50', 'it-warranty-tracker' ) );
		}
		if ( $times_on && ( $times_val < 1 || $times_val > 50 ) ) {
			return new \WP_Error( 'itwt_invalid', __( 'จำนวนครั้งที่ต่อได้ต้องอยู่ระหว่าง 1–50', 'it-warranty-tracker' ) );
		}

		$row = array(
			'service_tag'             => $service_tag,
			'brand'                   => sanitize_text_field( (string) ( $data['brand'] ?? '' ) ),
			'serial_number'           => $serial,
			'asset_type'              => $asset_type,
			'model_name'              => sanitize_text_field( (string) ( $data['model_name'] ?? '' ) ),
			'specs_json'              => is_string( $specs ) ? $specs : '[]',
			'customer_id'             => absint( $data['customer_id'] ?? 0 ),
			'install_location'        => sanitize_text_field( (string) ( $data['install_location'] ?? '' ) ),
			'status'                  => sanitize_key( (string) ( $data['status'] ?? 'active' ) ),
			'purchase_date'           => $purchase,
			'warranty_end_date'       => $warranty,
			'renew_max_years_enabled' => $years_on ? 1 : 0,
			'renew_max_years'         => $years_on ? $years_val : null,
			'renew_max_times_enabled' => $times_on ? 1 : 0,
			'renew_max_times'         => $times_on ? $times_val : null,
			'internal_note'           => sanitize_textarea_field( (string) ( $data['internal_note'] ?? '' ) ),
			'updated_by'              => $user,
			'updated_at'              => $now,
		);
		if ( ! in_array( $row['status'], array( 'active', 'void', 'decommissioned' ), true ) ) {
			$row['status'] = 'active';
		}
		if ( '' === $row['brand'] ) {
			return new \WP_Error( 'itwt_invalid', __( 'ต้องระบุยี่ห้อ', 'it-warranty-tracker' ) );
		}

		if ( $id > 0 ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			$this->bust_cache( $service_tag );
			return $id;
		}

		$row['created_by'] = $user;
		$row['created_at'] = $now;
		$ok                = $wpdb->insert( $table, $row );
		if ( false === $ok ) {
			Installer::migrate_asset_columns();
			$ok = $wpdb->insert( $table, $row );
		}
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'บันทึกทรัพย์สินไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		$this->bust_cache( $service_tag );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Parse Y-m-d or empty → null.
	 *
	 * @param string $date Raw date.
	 * @return string|null|\WP_Error
	 */
	private function nullable_date( string $date ) {
		$date = trim( $date );
		if ( '' === $date ) {
			return null;
		}
		$iso = \ITWT\Dates::parse_input( $date );
		if ( null === $iso ) {
			return new \WP_Error( 'itwt_invalid', __( 'รูปแบบวันที่ไม่ถูกต้อง (ใช้ วว/ดด/ปปปป)', 'it-warranty-tracker' ) );
		}
		return $iso;
	}

	/**
	 * Soft or hard delete.
	 *
	 * @param int $id Asset ID.
	 * @return true|\WP_Error
	 */
	public function delete( int $id ) {
		global $wpdb;
		$asset = $this->find( $id );
		if ( ! $asset ) {
			return new \WP_Error( 'itwt_missing', __( 'ไม่พบทรัพย์สิน', 'it-warranty-tracker' ) );
		}

		$has_related = $this->has_related_records( $id );
		if ( $has_related ) {
			$wpdb->update(
				Installer::table( 'assets' ),
				array(
					'status'     => 'decommissioned',
					'updated_at' => current_time( 'mysql' ),
					'updated_by' => get_current_user_id(),
				),
				array( 'id' => $id )
			);
			$this->bust_cache( (string) $asset->service_tag );
			return true;
		}

		$wpdb->delete( Installer::table( 'assets' ), array( 'id' => $id ), array( '%d' ) );
		$this->bust_cache( (string) $asset->service_tag );
		return true;
	}

	/**
	 * Whether asset has contracts / logs / requests.
	 *
	 * @param int $id Asset ID.
	 */
	public function has_related_records( int $id ): bool {
		global $wpdb;
		$tables = array( 'contracts', 'service_logs', 'support_requests', 'line_subscriptions' );
		foreach ( $tables as $suffix ) {
			$table = Installer::table( $suffix );
			$count = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE asset_id = %d", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
			if ( $count > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Dashboard coverage counts.
	 *
	 * @return array<string,int>
	 */
	public function coverage_counts(): array {
		$counts = array(
			'covered'        => 0,
			'expiring'       => 0,
			'expired'        => 0,
			'void'           => 0,
			'decommissioned' => 0,
		);
		$result = $this->list( array( 'per_page' => 50, 'page' => 1 ) );
		// For dashboard use dedicated SQL for accuracy.
		global $wpdb;
		$assets    = Installer::table( 'assets' );
		$contracts = Installer::table( 'contracts' );
		$today     = wp_date( 'Y-m-d' );
		$settings  = \ITWT\Settings::get();
		$expiring  = (int) ( $settings['expiring_days'] ?? 30 );
		$cutoff    = wp_date( 'Y-m-d', strtotime( '+' . $expiring . ' days' ) );

		$counts['void'] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$assets} WHERE status = %s", 'void' ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		$counts['decommissioned'] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$assets} WHERE status = %s", 'decommissioned' ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$counts['covered'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT a.id) FROM {$assets} a
				INNER JOIN {$contracts} c ON c.asset_id = a.id
				WHERE a.status = 'active' AND c.status = 'active'
				AND c.start_date <= %s AND c.end_date >= %s AND c.end_date > %s",
				$today,
				$today,
				$cutoff
			)
		);
		$counts['expiring'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT a.id) FROM {$assets} a
				INNER JOIN {$contracts} c ON c.asset_id = a.id
				WHERE a.status = 'active' AND c.status = 'active'
				AND c.start_date <= %s AND c.end_date >= %s AND c.end_date <= %s",
				$today,
				$today,
				$cutoff
			)
		);
		$counts['expired'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assets} a
				WHERE a.status = 'active'
				AND NOT EXISTS (
					SELECT 1 FROM {$contracts} c
					WHERE c.asset_id = a.id AND c.status = 'active'
					AND c.start_date <= %s AND c.end_date >= %s
				)",
				$today,
				$today
			)
		);
		// phpcs:enable

		unset( $result );
		return $counts;
	}

	/**
	 * Assets expiring within N days (by MAX end_date).
	 *
	 * @param int $within_days Days window.
	 * @param int $limit       Max rows.
	 * @return array<int,object>
	 */
	public function expiring_soon( int $within_days = 30, int $limit = 50 ): array {
		global $wpdb;
		$assets    = Installer::table( 'assets' );
		$contracts = Installer::table( 'contracts' );
		$today     = wp_date( 'Y-m-d' );
		$cutoff    = wp_date( 'Y-m-d', strtotime( '+' . $within_days . ' days' ) );
		$limit     = min( 100, max( 1, $limit ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.id, a.service_tag, a.brand, a.model_name, a.status, latest.max_end
				FROM {$assets} a
				INNER JOIN (
					SELECT asset_id, MAX(end_date) AS max_end
					FROM {$contracts}
					WHERE status = 'active'
					GROUP BY asset_id
				) latest ON latest.asset_id = a.id
				WHERE a.status = 'active'
				AND latest.max_end >= %s AND latest.max_end <= %s
				ORDER BY latest.max_end ASC
				LIMIT %d",
				$today,
				$cutoff,
				$limit
			)
		);
		// phpcs:enable

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Clear lookup cache for a tag.
	 *
	 * @param string $service_tag Tag.
	 */
	public function bust_cache( string $service_tag ): void {
		$tag = Service_Tag::normalize( $service_tag );
		wp_cache_delete( 'itwt_lookup_' . md5( $tag ), 'itwt' );
	}

	/**
	 * Check service tag existence.
	 *
	 * @param string $tag Tag.
	 */
	public function tag_exists( string $tag ): bool {
		return null !== $this->find_by_tag( $tag );
	}
}
