<?php
/**
 * Shared helpers for ITWT admin list screens.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Page-size and list chrome helpers shared by customer / asset / request lists.
 */
final class Admin_List {

	/**
	 * Allowed rows-per-page options.
	 *
	 * @return array<int,int>
	 */
	public static function per_page_options(): array {
		return array( 20, 50, 100 );
	}

	/**
	 * Clamp to an allowed page size (default 20).
	 *
	 * @param int $per_page Requested size.
	 */
	public static function normalize_per_page( int $per_page ): int {
		$allowed = self::per_page_options();
		if ( in_array( $per_page, $allowed, true ) ) {
			return $per_page;
		}
		return 20;
	}

	/**
	 * Read per_page from current request.
	 */
	public static function request_per_page(): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$raw = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 20;
		return self::normalize_per_page( $raw );
	}
}
