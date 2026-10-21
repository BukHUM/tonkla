<?php
/**
 * Installer facade delegating to legacy activation helpers.
 *
 * @package Tonkla\ImageOptimizer
 */

namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;

/**
 * Activation / deactivation delegation.
 */
final class Installer {

	public static function activate(): void {
		if ( function_exists( 'io_set_default_options' ) ) {
			io_set_default_options();
		}
		Settings::migrate();
	}

	public static function deactivate(): void {
		if ( function_exists( 'io_clear_transients' ) ) {
			io_clear_transients();
		}
	}
}
