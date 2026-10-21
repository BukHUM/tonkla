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
		self::maybe_migrate();
		$stored = get_option( 'itwt_settings', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	/**
	 * One-time settings migrations.
	 */
	private static function maybe_migrate(): void {
		$ver = (string) get_option( 'itwt_settings_version', '0' );
		if ( version_compare( $ver, '1.5.6', '>=' ) ) {
			return;
		}

		$stored = get_option( 'itwt_settings', array() );
		if ( is_array( $stored ) ) {
			// Hide public LINE notify button unless admin opts in.
			$stored['show_line_button'] = false;
			update_option( 'itwt_settings', $stored, false );
		}
		update_option( 'itwt_settings_version', '1.5.6', false );
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
	 * Media library attachment ID for the configured logo (0 if unset/invalid).
	 */
	public static function logo_attachment_id(): int {
		$id = (int) ( self::get()['logo_attachment_id'] ?? 0 );
		if ( $id > 0 && wp_attachment_is_image( $id ) ) {
			return $id;
		}
		return 0;
	}

	/**
	 * Logo image URL for sticker / portal.
	 * Fallback order: plugin setting → WP custom logo → theme Carbon main_logo → theme landscape file.
	 *
	 * @param string $size Attachment size when using media library.
	 */
	public static function logo_url( string $size = 'medium' ): string {
		$id = self::logo_attachment_id();
		if ( $id > 0 ) {
			$url = wp_get_attachment_image_url( $id, $size );
			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}

		$custom = (int) get_theme_mod( 'custom_logo' );
		if ( $custom > 0 ) {
			$url = wp_get_attachment_image_url( $custom, $size );
			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}

		if ( function_exists( 'carbon_get_theme_option' ) ) {
			$carbon = carbon_get_theme_option( 'main_logo' );
			if ( is_string( $carbon ) && '' !== $carbon ) {
				return esc_url_raw( $carbon );
			}
		}

		$path = get_theme_file_path( 'assets/images/logo-tonkla-landscape.png' );
		if ( is_string( $path ) && is_readable( $path ) ) {
			return get_theme_file_uri( 'assets/images/logo-tonkla-landscape.png' );
		}

		return '';
	}

	/**
	 * Whether to show developer credit / backlink.
	 *
	 * Always on: showing the developer credit is a condition of the free
	 * license, so the admin toggle is locked (rendered disabled) and this
	 * method intentionally ignores the stored setting.
	 */
	public static function show_developer_credit(): bool {
		return true;
	}

	/**
	 * Developer display name for credit links.
	 */
	public static function developer_name(): string {
		return 'ต้นกล้า ไอที';
	}

	/**
	 * Developer site URL with campaign UTM.
	 *
	 * @param string $context One of: admin, portal, plugins, help.
	 */
	public static function developer_credit_url( string $context = 'admin' ): string {
		$allowed = array( 'admin', 'portal', 'plugins', 'help' );
		$context = sanitize_key( $context );
		if ( ! in_array( $context, $allowed, true ) ) {
			$context = 'admin';
		}

		return add_query_arg(
			array(
				'utm_source'   => 'tonkla-suite',
				'utm_medium'   => 'credit',
				'utm_campaign' => 'warranty-tracker',
				'utm_content'  => $context,
			),
			'https://tonkla.co'
		);
	}

	/**
	 * Render developer credit markup when enabled.
	 *
	 * @param string $context One of: admin, portal, help.
	 */
	public static function render_developer_credit( string $context = 'admin' ): void {
		if ( ! self::show_developer_credit() ) {
			return;
		}
		$credit_context = sanitize_key( $context );
		include ITWT_PLUGIN_DIR . 'admin/views/partials/developer-credit.php';
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
			'company_name_th'          => 'ต้นกล้า ไอที',
			'company_name_en'          => 'TONKLA IT',
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
			'portal_title'             => '',
			'portal_description'       => '',
			'portal_accent_color'      => '',
			'portal_theme'             => 'soft',
			'privacy_policy_url'       => '',
			'show_masked_serial'       => true,
			'show_specs'               => true,
			'show_contracts'           => true,
			'show_service_logs'        => true,
			'show_line_button'         => false,
			'show_support_button'      => true,
			'show_purchase_date'       => false,
			'show_warranty_end'        => true,
			'show_sla'                 => true,
			'show_coverage_bar'        => true,
			'show_developer_credit'    => true,
		);
	}
}
