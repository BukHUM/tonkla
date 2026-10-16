<?php
/**
 * Settings helper.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Read / write itwt_settings option.
 */
final class Settings {

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function get(): array {
		$stored = get_option( 'itwt_settings', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	/**
	 * Update settings (partial merge).
	 *
	 * @param array<string,mixed> $data Settings to merge.
	 */
	public static function update( array $data ): void {
		$merged = array_merge( self::get(), $data );
		update_option( 'itwt_settings', $merged, false );
	}

	/**
	 * Public lookup URL for a service tag.
	 *
	 * @param string $service_tag Tag.
	 */
	public static function lookup_url( string $service_tag = '' ): string {
		$settings = self::get();
		$page_id  = (int) ( $settings['lookup_page_id'] ?? 0 );
		$url      = $page_id > 0 ? get_permalink( $page_id ) : home_url( '/' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = home_url( '/' );
		}
		if ( '' !== $service_tag ) {
			$url = add_query_arg( 'tag', $service_tag, $url );
		}
		return $url;
	}

	/**
	 * Default settings.
	 *
	 * @return array<string,mixed>
	 */
	private static function defaults(): array {
		return array(
			'logo_attachment_id'       => 0,
			'support_phone'            => '',
			'lookup_page_id'           => 0,
			'expiring_days'            => 30,
			'milestones'               => array( 60, 30, 7, 0 ),
			'request_retention_months' => 24,
			'line_tech_group_id'       => '',
			'line_pending_group_id'    => '',
			'line_oa_id'               => '',
			'line_monthly_quota'       => 300,
			'line_customer_reserve'    => 150,
			'pdpa_consent_text'        => '',
			'line_pdpa_text'           => '',
			'asset_types'              => Asset_Types::defaults(),
		);
	}
}
