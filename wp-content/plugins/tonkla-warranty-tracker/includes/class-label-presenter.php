<?php
/**
 * Present asset data for warranty sticker print layout.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Builds sticker fields from asset + contracts + label-flagged specs.
 */
final class Label_Presenter {

	/**
	 * Spec keys treated as the large hostname / machine title on the sticker.
	 *
	 * @var array<int,string>
	 */
	private const TITLE_KEYS = array(
		'hostname',
		'host',
		'computer name',
		'computer_name',
		'server name',
		'server_name',
		'ชื่อเครื่อง',
		'ชื่อโฮสต์',
		'ชื่อเซิร์ฟเวอร์',
	);

	/**
	 * Present one asset for print.
	 *
	 * @param object            $asset     Asset row.
	 * @param array<int,object> $contracts Contracts for this asset.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array{
	 *   service_tag:string,
	 *   asset_type:string,
	 *   type_label:string,
	 *   type_icon_svg:string,
	 *   title:string,
	 *   title_key:string,
	 *   subtitle:string,
	 *   purchase:string,
	 *   expire:string,
	 *   lines:array<int,array{key:string,value:string}>,
	 *   qr_url:string,
	 *   company_name_th:string,
	 *   company_name_en:string,
	 *   logo_id:int,
	 *   logo_url:string,
	 *   phone:string
	 * }
	 */
	public static function present( object $asset, array $contracts, array $settings = array() ): array {
		if ( empty( $settings ) ) {
			$settings = Settings::get();
		}

		$specs = array();
		if ( ! empty( $asset->specs_json ) ) {
			$decoded = is_string( $asset->specs_json )
				? json_decode( (string) $asset->specs_json, true )
				: $asset->specs_json;
			$specs = is_array( $decoded ) ? $decoded : array();
		}

		$title     = '';
		$title_key = '';
		$lines     = array();
		foreach ( $specs as $row ) {
			if ( ! is_array( $row ) || empty( $row['on_label'] ) ) {
				continue;
			}
			$key   = trim( (string) ( $row['key'] ?? '' ) );
			$value = trim( (string) ( $row['value'] ?? '' ) );
			if ( '' === $key || '' === $value ) {
				continue;
			}
			if ( '' === $title && self::is_title_key( $key ) ) {
				$title     = $value;
				$title_key = $key;
				continue;
			}
			$lines[] = array(
				'key'   => $key,
				'value' => $value,
			);
		}

		$coverage = Coverage::compute( (string) ( $asset->status ?? 'active' ), $contracts );
		$purchase = self::format_month_year( (string) ( $asset->purchase_date ?? '' ) );
		$expire   = self::format_month_year( (string) ( $asset->warranty_end_date ?? '' ) );
		if ( '' === $expire && ! empty( $coverage['latest_end'] ) ) {
			$expire = self::format_month_year( (string) $coverage['latest_end'] );
		}
		if ( '' === $purchase ) {
			$purchase = self::earliest_contract_start_month( $contracts );
		}

		$asset_type = (string) ( $asset->asset_type ?? '' );

		return array(
			'service_tag'     => (string) ( $asset->service_tag ?? '' ),
			'asset_type'      => $asset_type,
			'type_label'      => Asset_Types::label( $asset_type ),
			'type_icon_svg'   => Asset_Types::icon_svg( $asset_type ),
			'title'           => $title,
			'title_key'       => $title_key,
			'subtitle'        => (string) ( $asset->model_name ?? '' ),
			'purchase'        => $purchase,
			'expire'          => $expire,
			'lines'           => $lines,
			'qr_url'          => Settings::lookup_url( (string) ( $asset->service_tag ?? '' ) ),
			'company_name_th' => (string) ( $settings['company_name_th'] ?? '' ),
			'company_name_en' => (string) ( $settings['company_name_en'] ?? '' ),
			'logo_id'         => Settings::logo_attachment_id(),
			'logo_url'        => Settings::logo_url( 'medium' ),
			'phone'           => (string) ( $settings['support_phone'] ?? '' ),
		);
	}

	/**
	 * Whether a spec key should be used as the large title.
	 *
	 * @param string $key Spec key.
	 */
	public static function is_title_key( string $key ): bool {
		$normalized = strtolower( trim( preg_replace( '/\s+/u', ' ', $key ) ?? $key ) );
		$normalized = str_replace( array( '-', '_' ), ' ', $normalized );
		$normalized = trim( preg_replace( '/\s+/u', ' ', $normalized ) ?? $normalized );
		foreach ( self::TITLE_KEYS as $alias ) {
			if ( $normalized === strtolower( $alias ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Format Y-m-d (or partial) as MM/YYYY.
	 *
	 * @param string $date Date string.
	 */
	public static function format_month_year( string $date ): string {
		$date = trim( $date );
		if ( '' === $date ) {
			return '';
		}
		if ( preg_match( '/^(\d{4})-(\d{2})/', $date, $m ) ) {
			return $m[2] . '/' . $m[1];
		}
		if ( preg_match( '/^(\d{1,2})\/(\d{4})$/', $date, $m ) ) {
			return str_pad( $m[1], 2, '0', STR_PAD_LEFT ) . '/' . $m[2];
		}
		$ts = strtotime( $date );
		if ( false === $ts ) {
			return '';
		}
		return wp_date( 'm/Y', $ts );
	}

	/**
	 * Earliest active/any contract start as MM/YYYY.
	 *
	 * @param array<int,object> $contracts Contracts.
	 */
	private static function earliest_contract_start_month( array $contracts ): string {
		$earliest = null;
		foreach ( $contracts as $c ) {
			$start = (string) ( $c->start_date ?? '' );
			if ( '' === $start ) {
				continue;
			}
			if ( null === $earliest || $start < $earliest ) {
				$earliest = $start;
			}
		}
		return null !== $earliest ? self::format_month_year( $earliest ) : '';
	}
}
