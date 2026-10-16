<?php
/**
 * Daily warranty expiry LINE notifier.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Line;

defined( 'ABSPATH' ) || exit;

use ITWT\Coverage;
use ITWT\Public_Presenter;
use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Contract_Repo;
use ITWT\Repositories\Service_Log_Repo;
use ITWT\Repositories\Subscription_Repo;
use ITWT\Settings;

/**
 * Cron job: notify customers of upcoming warranty end.
 */
final class Expiry_Notifier {

	/**
	 * Run daily batch.
	 */
	public function run(): void {
		$settings   = Settings::get();
		$milestones = $settings['milestones'] ?? array( 60, 30, 7, 0 );
		if ( ! is_array( $milestones ) ) {
			$milestones = array( 60, 30, 7, 0 );
		}

		$contract_repo = new Contract_Repo();
		$sub_repo      = new Subscription_Repo();
		$asset_repo    = new Asset_Repo();
		$quota         = new Line_Quota();
		$client        = new Line_Client();

		// Group messages per LINE user for the day.
		$by_user = array();

		foreach ( $milestones as $milestone ) {
			$milestone = (int) $milestone;
			$contracts = $contract_repo->for_milestone( $milestone, 200 );
			foreach ( $contracts as $contract ) {
				$asset = $asset_repo->find( (int) $contract->asset_id );
				if ( ! $asset || 'active' !== $asset->status ) {
					continue;
				}
				$subs = $sub_repo->active_for_asset( (int) $asset->id );
				foreach ( $subs as $sub ) {
					if ( $sub_repo->already_notified( (int) $sub->id, (int) $contract->id, $milestone ) ) {
						continue;
					}
					$user = (string) $sub->line_user_id;
					if ( ! isset( $by_user[ $user ] ) ) {
						$by_user[ $user ] = array();
					}
					$by_user[ $user ][] = array(
						'sub'       => $sub,
						'contract'  => $contract,
						'asset'     => $asset,
						'milestone' => $milestone,
					);
				}
			}
		}

		$sent_users = 0;
		foreach ( $by_user as $user_id => $items ) {
			if ( $sent_users >= 200 ) {
				break;
			}
			if ( ! $quota->can_push_customer() ) {
				foreach ( $items as $item ) {
					$sub_repo->log_notification( (int) $item['sub']->id, (int) $item['contract']->id, (int) $item['milestone'], 'failed' );
				}
				continue;
			}

			$text = $this->build_digest( $items );
			$result = $client->push( (string) $user_id, array( Line_Client::text_message( $text ) ) );

			foreach ( $items as $item ) {
				$status = is_wp_error( $result ) ? 'failed' : 'sent';
				// Only log "sent" on UNIQUE success; failed can retry next day if not inserted as sent.
				if ( 'sent' === $status ) {
					$sub_repo->log_notification( (int) $item['sub']->id, (int) $item['contract']->id, (int) $item['milestone'], 'sent' );
				} else {
					$sub_repo->log_notification( (int) $item['sub']->id, (int) $item['contract']->id, (int) $item['milestone'], 'failed' );
				}
			}

			if ( ! is_wp_error( $result ) ) {
				$quota->record_push( 1 );
				++$sent_users;
			}
		}
	}

	/**
	 * Build one digest message for a user.
	 *
	 * @param array<int,array<string,mixed>> $items Items for one user.
	 */
	private function build_digest( array $items ): string {
		$lines = array( __( 'แจ้งเตือนสถานะประกัน/MA', 'it-warranty-tracker' ) );
		$log_repo = new Service_Log_Repo();
		$contract_repo = new Contract_Repo();

		foreach ( $items as $item ) {
			$asset     = $item['asset'];
			$milestone = (int) $item['milestone'];
			$contracts = $contract_repo->for_asset( (int) $asset->id );
			$logs      = $log_repo->for_asset( (int) $asset->id, true );
			$presented = Public_Presenter::present_asset( $asset, $contracts, $logs );
			$label     = Coverage::label( $presented['coverage']['status'], $presented['coverage']['days_remaining'] );
			$url       = Settings::lookup_url( (string) $asset->service_tag );

			$lines[] = sprintf(
				"- %s / %s\n  %s (อีก %d วัน)\n  %s",
				(string) $asset->service_tag,
				(string) $asset->model_name,
				$label,
				$milestone,
				$url
			);
		}

		return implode( "\n\n", $lines );
	}
}
