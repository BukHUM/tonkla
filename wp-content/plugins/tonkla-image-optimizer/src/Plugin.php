<?php
/**
 * Tonkla Image Optimizer — namespaced plugin facade (Phase 2 bridge).
 *
 * Legacy IO_* classes remain the runtime implementation until Phase 3;
 * this class is the new entry point and delegates to them.
 *
 * @package Tonkla\ImageOptimizer
 */

namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin facade.
 */
final class Plugin {

	/**
	 * Boot core singletons via legacy implementations.
	 */
	public static function boot(): void {
		if ( class_exists( 'IO_Image_Optimizer' ) ) {
			\IO_Image_Optimizer::get_instance();
		}
		if ( class_exists( 'IO_WebP_Converter' ) ) {
			\IO_WebP_Converter::get_instance();
		}
	}

	/**
	 * Plugin version.
	 */
	public static function version(): string {
		return defined( 'TONKLA_IMG_VERSION' ) ? (string) TONKLA_IMG_VERSION : '';
	}
}
