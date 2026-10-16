<?php
/**
 * LINE subscription and notification log repository.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Repositories;

defined( 'ABSPATH' ) || exit;

use ITWT\Installer;

/**
 * CRUD for line_subscriptions and notification_log.
 */
final class Subscription_Repo {

	/**
	 * Find subscription by ID.
	 *
	 * @param int $id Subscription ID.
	 */
	public function find( int $id ): ?object {
		global $wpdb;
		$table = Installer::table( 'line_subscriptions' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		return $row ?: null;
	}

	/**
	 * Upsert active subscription.
	 *
	 * @param int    $asset_id     Asset ID.
	 * @param string $line_user_id LINE user ID.
	 * @return int|\WP_Error
	 */
	public function subscribe( int $asset_id, string $line_user_id ) {
		global $wpdb;
		$table = Installer::table( 'line_subscriptions' );
		$now   = current_time( 'mysql' );
		$line_user_id = sanitize_text_field( $line_user_id );

		if ( $asset_id < 1 || '' === $line_user_id ) {
			return new \WP_Error( 'itwt_invalid', __( 'ข้อมูลสมัครไม่ครบ', 'it-warranty-tracker' ) );
		}

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE asset_id = %d AND line_user_id = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id,
				$line_user_id
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'status'     => 'active',
					'consent_at' => $now,
					'updated_at' => $now,
				),
				array( 'id' => (int) $existing->id )
			);
			return (int) $existing->id;
		}

		$ok = $wpdb->insert(
			$table,
			array(
				'asset_id'     => $asset_id,
				'line_user_id' => $line_user_id,
				'status'       => 'active',
				'consent_at'   => $now,
				'created_at'   => $now,
				'updated_at'   => $now,
			)
		);
		if ( false === $ok ) {
			return new \WP_Error( 'itwt_db', __( 'สมัครรับแจ้งเตือนไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Unsubscribe all for a LINE user.
	 *
	 * @param string $line_user_id LINE user ID.
	 */
	public function unsubscribe_user( string $line_user_id ): int {
		global $wpdb;
		$table = Installer::table( 'line_subscriptions' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET status = 'unsubscribed', updated_at = %s WHERE line_user_id = %s AND status = 'active'",
				current_time( 'mysql' ),
				sanitize_text_field( $line_user_id )
			)
		);
	}

	/**
	 * Active subscriptions for an asset.
	 *
	 * @param int $asset_id Asset ID.
	 * @return array<int,object>
	 */
	public function active_for_asset( int $asset_id ): array {
		global $wpdb;
		$table = Installer::table( 'line_subscriptions' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE asset_id = %d AND status = 'active' LIMIT 100", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$asset_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Record notification attempt (UNIQUE prevents duplicate sent).
	 * Failed rows may be updated to sent on retry.
	 *
	 * @param int    $subscription_id Subscription ID.
	 * @param int    $contract_id     Contract ID.
	 * @param int    $milestone       Milestone days.
	 * @param string $result          sent|failed.
	 * @return bool True if written.
	 */
	public function log_notification( int $subscription_id, int $contract_id, int $milestone, string $result ): bool {
		global $wpdb;
		$table  = Installer::table( 'notification_log' );
		$result = sanitize_key( $result );
		$now    = current_time( 'mysql' );

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, result FROM {$table} WHERE subscription_id = %d AND contract_id = %d AND milestone = %d LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$subscription_id,
				$contract_id,
				$milestone
			)
		);

		if ( $existing ) {
			if ( 'sent' === $existing->result ) {
				return false;
			}
			$ok = $wpdb->update(
				$table,
				array(
					'result'  => $result,
					'sent_at' => $now,
				),
				array( 'id' => (int) $existing->id )
			);
			return false !== $ok;
		}

		$ok = $wpdb->insert(
			$table,
			array(
				'subscription_id' => $subscription_id,
				'contract_id'     => $contract_id,
				'milestone'       => $milestone,
				'result'          => $result,
				'sent_at'         => $now,
			)
		);
		return false !== $ok;
	}

	/**
	 * Whether a successful notification already logged.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @param int $contract_id     Contract ID.
	 * @param int $milestone       Milestone.
	 */
	public function already_notified( int $subscription_id, int $contract_id, int $milestone ): bool {
		global $wpdb;
		$table = Installer::table( 'notification_log' );
		$id    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE subscription_id = %d AND contract_id = %d AND milestone = %d AND result = 'sent' LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$subscription_id,
				$contract_id,
				$milestone
			)
		);
		return null !== $id;
	}

	/**
	 * Purge old notification logs (older than 12 months).
	 */
	public function purge_old_logs(): int {
		global $wpdb;
		$table  = Installer::table( 'notification_log' );
		$cutoff = wp_date( 'Y-m-d H:i:s', strtotime( '-12 months' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$table} WHERE sent_at < %s LIMIT 500", $cutoff )
		);
		return is_int( $deleted ) ? $deleted : 0;
	}
}
