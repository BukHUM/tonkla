<?php
/**
 * Spec row icon keys for public portal.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Allowed icon slugs for asset specs.
 */
final class Spec_Icons {

	/**
	 * Icon options for admin select (slug => label).
	 *
	 * @return array<string,string>
	 */
	public static function options(): array {
		return array(
			''          => __( '— ไม่มีไอคอน —', 'tonkla-warranty-tracker' ),
			'cpu'       => __( 'CPU', 'tonkla-warranty-tracker' ),
			'memory'    => __( 'Memory / RAM', 'tonkla-warranty-tracker' ),
			'storage'   => __( 'Storage', 'tonkla-warranty-tracker' ),
			'mainboard' => __( 'Mainboard (Motherboard)', 'tonkla-warranty-tracker' ),
			'chassis'   => __( 'Chassis (Case)', 'tonkla-warranty-tracker' ),
			'power'     => __( 'Power', 'tonkla-warranty-tracker' ),
			'network'   => __( 'Network', 'tonkla-warranty-tracker' ),
			'os'        => __( 'OS / Software', 'tonkla-warranty-tracker' ),
			'other'     => __( 'อื่น ๆ', 'tonkla-warranty-tracker' ),
		);
	}

	/**
	 * Sanitize icon slug.
	 *
	 * @param string $icon Raw.
	 */
	public static function sanitize( string $icon ): string {
		$icon = sanitize_key( $icon );
		$keys = array_keys( self::options() );
		return in_array( $icon, $keys, true ) ? $icon : '';
	}

	/**
	 * Guess icon from spec key label.
	 *
	 * @param string $key Spec key.
	 */
	public static function guess_from_key( string $key ): string {
		$k = strtolower( $key );
		if ( str_contains( $k, 'cpu' ) || str_contains( $k, 'processor' ) || str_contains( $k, 'โปรเซส' ) ) {
			return 'cpu';
		}
		if ( str_contains( $k, 'ram' ) || str_contains( $k, 'memory' ) || str_contains( $k, 'เมม' ) ) {
			return 'memory';
		}
		if ( str_contains( $k, 'ssd' ) || str_contains( $k, 'hdd' ) || str_contains( $k, 'nvme' ) || str_contains( $k, 'storage' ) || str_contains( $k, 'disk' ) || str_contains( $k, 'ดิสก์' ) ) {
			return 'storage';
		}
		if ( str_contains( $k, 'mainboard' ) || str_contains( $k, 'motherboard' ) || str_contains( $k, 'เมนบอร์ด' ) || preg_match( '/\bmb\b/', $k ) ) {
			return 'mainboard';
		}
		if ( str_contains( $k, 'chassis' ) || str_contains( $k, 'case' ) || str_contains( $k, 'เคส' ) || str_contains( $k, 'ตู้' ) ) {
			return 'chassis';
		}
		if ( str_contains( $k, 'psu' ) || str_contains( $k, 'power' ) || str_contains( $k, 'watt' ) || str_contains( $k, 'ไฟ' ) ) {
			return 'power';
		}
		if ( str_contains( $k, 'nic' ) || str_contains( $k, 'network' ) || str_contains( $k, 'lan' ) || str_contains( $k, 'ethernet' ) ) {
			return 'network';
		}
		if ( str_contains( $k, 'os' ) || str_contains( $k, 'windows' ) || str_contains( $k, 'linux' ) || str_contains( $k, 'proxmox' ) || str_contains( $k, 'vmware' ) ) {
			return 'os';
		}
		return '';
	}
}
