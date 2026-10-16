<?php
/**
 * Shared Thai labels for enums shown in admin / public UI.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Human-readable labels for stored enum keys.
 */
final class Labels {

	/**
	 * Asset lifecycle status.
	 *
	 * @param string $status Status key.
	 */
	public static function asset_status( string $status ): string {
		$map = array(
			'active'         => __( 'ใช้งาน', 'it-warranty-tracker' ),
			'void'           => __( 'โมฆะ', 'it-warranty-tracker' ),
			'decommissioned' => __( 'เลิกใช้งาน', 'it-warranty-tracker' ),
		);
		return $map[ $status ] ?? $status;
	}

	/**
	 * Contract type.
	 *
	 * @param string $type Type key.
	 */
	public static function contract_type( string $type ): string {
		$map = array(
			'manufacturer' => __( 'ประกันผู้ผลิต', 'it-warranty-tracker' ),
			'warranty'     => __( 'ประกันบริษัท', 'it-warranty-tracker' ),
			'ma'           => __( 'MA', 'it-warranty-tracker' ),
		);
		return $map[ $type ] ?? $type;
	}

	/**
	 * Contract row status.
	 *
	 * @param string $status Status key.
	 */
	public static function contract_status( string $status ): string {
		$map = array(
			'active'    => __( 'ใช้งาน', 'it-warranty-tracker' ),
			'cancelled' => __( 'ยกเลิก', 'it-warranty-tracker' ),
		);
		return $map[ $status ] ?? $status;
	}

	/**
	 * Service log type.
	 *
	 * @param string $type Type key.
	 */
	public static function service_type( string $type ): string {
		$map = array(
			'claim'    => __( 'เคลม', 'it-warranty-tracker' ),
			'pm'       => __( 'บำรุงรักษา (PM)', 'it-warranty-tracker' ),
			'upgrade'  => __( 'อัปเกรด', 'it-warranty-tracker' ),
			'incident' => __( 'เหตุการณ์', 'it-warranty-tracker' ),
		);
		return $map[ $type ] ?? $type;
	}

	/**
	 * Support request status.
	 *
	 * @param string $status Status key.
	 */
	public static function request_status( string $status ): string {
		$map = array(
			'new'         => __( 'ใหม่', 'it-warranty-tracker' ),
			'in_progress' => __( 'กำลังดำเนินการ', 'it-warranty-tracker' ),
			'done'        => __( 'เสร็จสิ้น', 'it-warranty-tracker' ),
			'spam'        => __( 'สแปม', 'it-warranty-tracker' ),
		);
		return $map[ $status ] ?? $status;
	}

	/**
	 * Support request type.
	 *
	 * @param string $type Type key.
	 */
	public static function request_type( string $type ): string {
		return 'renew_ma' === $type
			? __( 'ขอต่อ MA', 'it-warranty-tracker' )
			: __( 'แจ้งซ่อม', 'it-warranty-tracker' );
	}

	/**
	 * Customer entity type (นิติบุคคล / บุคคลธรรมดา …).
	 *
	 * @param string $type Type key.
	 */
	public static function customer_type( string $type ): string {
		$map = self::customer_types();
		return $map[ $type ] ?? $type;
	}

	/**
	 * All customer types for selects.
	 *
	 * @return array<string,string>
	 */
	public static function customer_types(): array {
		return array(
			'individual' => __( 'บุคคลธรรมดา', 'it-warranty-tracker' ),
			'juristic'   => __( 'นิติบุคคล', 'it-warranty-tracker' ),
			'nonprofit'  => __( 'องค์กรไม่แสวงหาผลกำไร', 'it-warranty-tracker' ),
			'government' => __( 'หน่วยงานราชการ', 'it-warranty-tracker' ),
			'other'      => __( 'อื่น ๆ', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Party role label (ลูกค้า / คู่ค้า).
	 *
	 * @param string $role Role key.
	 */
	public static function party_role( string $role ): string {
		$map = self::party_roles();
		return $map[ $role ] ?? $role;
	}

	/**
	 * All party roles for selects / filters.
	 *
	 * @return array<string,string>
	 */
	public static function party_roles(): array {
		return array(
			'customer' => __( 'ลูกค้า', 'it-warranty-tracker' ),
			'partner'  => __( 'คู่ค้า', 'it-warranty-tracker' ),
			'both'     => __( 'ลูกค้าและคู่ค้า', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Business location label.
	 *
	 * @param string $location Location key.
	 */
	public static function business_location( string $location ): string {
		$map = self::business_locations();
		return $map[ $location ] ?? $location;
	}

	/**
	 * Business locations.
	 *
	 * @return array<string,string>
	 */
	public static function business_locations(): array {
		return array(
			'thai'    => __( 'ไทย', 'it-warranty-tracker' ),
			'foreign' => __( 'ต่างประเทศ', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Format public request reference e.g. REQ-2026-0042.
	 *
	 * @param int $id Request ID.
	 */
	public static function request_reference( int $id ): string {
		$year = wp_date( 'Y' );
		return sprintf( 'REQ-%s-%04d', $year, $id );
	}

	/**
	 * All asset statuses for selects.
	 *
	 * @return array<string,string>
	 */
	public static function asset_statuses(): array {
		return array(
			'active'         => self::asset_status( 'active' ),
			'void'           => self::asset_status( 'void' ),
			'decommissioned' => self::asset_status( 'decommissioned' ),
		);
	}

	/**
	 * All contract types for selects.
	 *
	 * @return array<string,string>
	 */
	public static function contract_types(): array {
		return array(
			'manufacturer' => self::contract_type( 'manufacturer' ),
			'warranty'     => self::contract_type( 'warranty' ),
			'ma'           => self::contract_type( 'ma' ),
		);
	}

	/**
	 * All service types for selects.
	 *
	 * @return array<string,string>
	 */
	public static function service_types(): array {
		return array(
			'claim'    => self::service_type( 'claim' ),
			'pm'       => self::service_type( 'pm' ),
			'upgrade'  => self::service_type( 'upgrade' ),
			'incident' => self::service_type( 'incident' ),
		);
	}

	/**
	 * All request statuses for selects.
	 *
	 * @return array<string,string>
	 */
	public static function request_statuses(): array {
		return array(
			'new'         => self::request_status( 'new' ),
			'in_progress' => self::request_status( 'in_progress' ),
			'done'        => self::request_status( 'done' ),
			'spam'        => self::request_status( 'spam' ),
		);
	}
}
