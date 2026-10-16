<?php
/**
 * Notify technician LINE group of new support requests.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT\Line;

defined( 'ABSPATH' ) || exit;

use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Support_Request_Repo;
use ITWT\Settings;

/**
 * Background notifier for tech group.
 */
final class Tech_Notifier {

	/**
	 * Cron callback after a new support request.
	 *
	 * @param int $request_id Support request ID.
	 */
	public function notify( int $request_id ): void {
		$request = ( new Support_Request_Repo() )->find( $request_id );
		if ( ! $request ) {
			return;
		}

		$settings = Settings::get();
		$group_id = (string) ( $settings['line_tech_group_id'] ?? '' );
		if ( '' === $group_id ) {
			return;
		}

		$quota = new Line_Quota();
		if ( ! $quota->can_push_tech( 5 ) ) {
			set_transient( 'itwt_admin_notice_quota', 1, DAY_IN_SECONDS );
			return;
		}

		$asset = ( new Asset_Repo() )->find( (int) $request->asset_id );
		if ( ! $asset ) {
			return;
		}

		$type_label = 'renew_ma' === $request->request_type
			? __( 'ขอต่อ MA', 'it-warranty-tracker' )
			: __( 'แจ้งซ่อม', 'it-warranty-tracker' );

		$admin_url = admin_url( 'admin.php?page=itwt-requests&request_id=' . $request_id );

		$text = sprintf(
			"%s\nService Tag: %s\n%s\n%s / %s\n%s",
			$type_label,
			(string) $asset->service_tag,
			(string) $asset->model_name,
			(string) $request->contact_name,
			(string) $request->phone,
			$admin_url
		);

		$client = new Line_Client();
		$result = $client->push( $group_id, array( Line_Client::text_message( $text ) ) );
		if ( ! is_wp_error( $result ) ) {
			$quota->record_push( 5 );
		}
	}

	/**
	 * Schedule background notify.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function schedule( int $request_id ): void {
		wp_schedule_single_event( time() + 5, 'itwt_notify_tech_group', array( $request_id ) );
	}
}
