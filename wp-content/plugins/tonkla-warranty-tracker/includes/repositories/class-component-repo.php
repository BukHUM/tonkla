<?php
/**
 * Asset component (purchase source / OEM warranty) repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;

/**
 * CRUD for itwt_asset_components.
 */
final class Component_Repo {

	/**
	 * List components for an asset.
	 *
	 * @param int $asset_id Asset ID.
	 * @return array<int,object>
	 */
	public function for_asset( int $asset_id ): array {
		global $wpdb;
		if ( $asset_id < 1 ) {
			return array();
		}
		$table = Installer::table( 'asset_components' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, asset_id, item_name, source, warranty_end, note, sort_order
				FROM {$table}
				WHERE asset_id = %d
				ORDER BY sort_order ASC, id ASC
				LIMIT 100", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Replace all component rows for an asset.
	 *
	 * @param int                      $asset_id Asset ID.
	 * @param array<int,array<string,mixed>> $rows     Normalized rows.
	 * @return true|\WP_Error
	 */
	public function replace_for_asset( int $asset_id, array $rows ) {
		global $wpdb;
		if ( $asset_id < 1 ) {
			return new \WP_Error( 'itwt_invalid', __( 'ทรัพย์สินไม่ถูกต้อง', 'tonkla-warranty-tracker' ) );
		}

		$table = Installer::table( 'asset_components' );
		$now   = current_time( 'mysql' );

		$wpdb->delete( $table, array( 'asset_id' => $asset_id ), array( '%d' ) );

		$order = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$item = sanitize_text_field( (string) ( $row['item_name'] ?? '' ) );
			$src  = sanitize_text_field( (string) ( $row['source'] ?? '' ) );
			$end  = sanitize_text_field( (string) ( $row['warranty_end'] ?? '' ) );
			$note = sanitize_text_field( (string) ( $row['note'] ?? '' ) );

			if ( '' === $item && '' === $src && '' === $end && '' === $note ) {
				continue;
			}
			if ( '' === $item ) {
				continue;
			}

			$warranty_end = null;
			if ( '' !== $end ) {
				$iso = \ITWT\Dates::parse_input( $end );
				if ( null === $iso ) {
					return new \WP_Error( 'itwt_invalid', __( 'วันหมดประกันต้นทางของอุปกรณ์ไม่ถูกต้อง (ใช้ วว/ดด/ปปปป)', 'tonkla-warranty-tracker' ) );
				}
				$warranty_end = $iso;
			}

			$insert = array(
				'asset_id'   => $asset_id,
				'item_name'  => $item,
				'source'     => $src,
				'note'       => function_exists( 'mb_substr' ) ? mb_substr( $note, 0, 500 ) : substr( $note, 0, 500 ),
				'sort_order' => $order,
				'created_at' => $now,
				'updated_at' => $now,
			);
			if ( null !== $warranty_end ) {
				$insert['warranty_end'] = $warranty_end;
			}
			$ok = $wpdb->insert( $table, $insert );
			if ( false === $ok ) {
				return new \WP_Error( 'itwt_db', __( 'บันทึกรายการอุปกรณ์ไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
			}
			++$order;
			if ( $order >= 100 ) {
				break;
			}
		}

		return true;
	}
}
