<?php
/**
 * Plugin Name:       IT Warranty Tracker
 * Plugin URI:        https://tonkla.co
 * Description:       IT asset tracking and warranty / MA lookup with LINE notifications.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            ต้นกล้า ไอที
 * Author URI:        https://tonkla.co
 * License:           GPL-2.0-or-later
 * Text Domain:       it-warranty-tracker
 * Domain Path:       /languages
 *
 * @package ITWT
 */

defined( 'ABSPATH' ) || exit;

define( 'ITWT_VERSION', '1.0.0' );
define( 'ITWT_DB_VERSION', '1.0.0' );
define( 'ITWT_PLUGIN_FILE', __FILE__ );
define( 'ITWT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITWT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ITWT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 style autoloader for ITWT namespace.
 *
 * @param string $class Fully-qualified class name.
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'ITWT\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$parts    = explode( '\\', $relative );
		$class_name = array_pop( $parts );
		$file_slug  = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		$subpath = '';
		foreach ( $parts as $part ) {
			$subpath .= strtolower( $part ) . '/';
		}

		$candidates = array(
			ITWT_PLUGIN_DIR . 'includes/' . $subpath . $file_slug,
			ITWT_PLUGIN_DIR . 'admin/' . $subpath . $file_slug,
		);

		// Map Admin\* to admin/, Line\* / Repositories\* stay under includes/.
		if ( 'Admin' === ( $parts[0] ?? '' ) ) {
			$admin_parts = array_slice( $parts, 1 );
			$admin_path  = '';
			foreach ( $admin_parts as $part ) {
				$admin_path .= strtolower( $part ) . '/';
			}
			$candidates = array(
				ITWT_PLUGIN_DIR . 'admin/' . $admin_path . $file_slug,
			);
		} elseif ( 'Line' === ( $parts[0] ?? '' ) ) {
			$candidates = array(
				ITWT_PLUGIN_DIR . 'includes/line/' . $file_slug,
			);
		} elseif ( 'Repositories' === ( $parts[0] ?? '' ) ) {
			$candidates = array(
				ITWT_PLUGIN_DIR . 'includes/repositories/' . $file_slug,
			);
		}

		foreach ( $candidates as $file ) {
			if ( is_readable( $file ) ) {
				require_once $file;
				return;
			}
		}
	}
);

register_activation_hook( __FILE__, array( 'ITWT\\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ITWT\\Installer', 'deactivate' ) );

/**
 * Boot the plugin after plugins_loaded.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'it-warranty-tracker', false, dirname( ITWT_PLUGIN_BASENAME ) . '/languages' );
		ITWT\Installer::maybe_upgrade();
		ITWT\Plugin::instance()->init();
	}
);
