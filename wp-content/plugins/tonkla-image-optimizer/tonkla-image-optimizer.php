<?php
/**
 * Image Optimization
 *
 * @package Image_Optimization
 * @version 2.0.3
 *
 * Copyright (C) ต้นกล้า ไอที — https://tonkla.co
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

/**
 * Plugin Name: Tonkla Image Optimizer
 * Plugin URI: https://tonkla.co
 * Description: Optimize images automatically with resize, WebP conversion, and cleanup unused images. Part of Tonkla Suite by ต้นกล้า ไอที.
 * Version: 2.0.3
 * Author: ต้นกล้า ไอที
 * Author URI: https://tonkla.co
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: tonkla-image-optimizer
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 8.1
 * Network: false
 * Update URI: false
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Tonkla Suite guard: do not run alongside the legacy image-optimization copy. */
if ( defined( 'IO_VERSION' ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Tonkla Image Optimizer is already active from another folder. Please deactivate the legacy copy.', 'tonkla-image-optimizer' ) . '</p></div>';
		}
	);
	return;
}

/**
 * Plugin version
 */
define( 'IO_VERSION', '2.0.3' );

/**
 * Plugin directory path
 */
define( 'IO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL
 */
define( 'IO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename
 */
define( 'IO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin file path
 */
define( 'IO_PLUGIN_FILE', __FILE__ );

/**
 * Minimum WordPress version required
 */
define( 'IO_MIN_WP_VERSION', '6.0' );

/**
 * Minimum PHP version required
 */
define( 'IO_MIN_PHP_VERSION', '8.1' );

/* Tonkla Suite constants (Phase 2 bridge) — new source of truth, IO_* kept as aliases. */
define( 'TONKLA_IMG_VERSION', IO_VERSION );
define( 'TONKLA_IMG_PLUGIN_DIR', IO_PLUGIN_DIR );
define( 'TONKLA_IMG_PLUGIN_URL', IO_PLUGIN_URL );
define( 'TONKLA_IMG_PLUGIN_BASENAME', IO_PLUGIN_BASENAME );
define( 'TONKLA_IMG_PLUGIN_FILE', IO_PLUGIN_FILE );
define( 'TONKLA_IMG_MIN_WP_VERSION', IO_MIN_WP_VERSION );
define( 'TONKLA_IMG_MIN_PHP_VERSION', IO_MIN_PHP_VERSION );

/**
 * PSR-4 autoloader for Tonkla\ImageOptimizer\ (src/).
 *
 * @param string $class_name Fully-qualified class name.
 */
function tonkla_img_autoloader( $class_name ) {
	$prefix = 'Tonkla\\ImageOptimizer\\';
	if ( strpos( $class_name, $prefix ) !== 0 ) {
		return;
	}
	$relative = substr( $class_name, strlen( $prefix ) );
	$file     = TONKLA_IMG_PLUGIN_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}
spl_autoload_register( 'tonkla_img_autoloader' );

/**
 * The code that runs during plugin activation.
 */
function io_activate() {
	// Check WordPress version
	if ( version_compare( get_bloginfo( 'version' ), IO_MIN_WP_VERSION, '<' ) ) {
		deactivate_plugins( IO_PLUGIN_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: WordPress version, 2: Required WordPress version */
				__( 'Image Optimization requires WordPress %1$s or higher. You are running WordPress %2$s. Please upgrade and try again.', 'tonkla-image-optimizer' ),
				IO_MIN_WP_VERSION,
				get_bloginfo( 'version' )
			)
		);
	}

	// Check PHP version
	if ( version_compare( PHP_VERSION, IO_MIN_PHP_VERSION, '<' ) ) {
		deactivate_plugins( IO_PLUGIN_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: PHP version, 2: Required PHP version */
				__( 'Image Optimization requires PHP %1$s or higher. You are running PHP %2$s. Please upgrade and try again.', 'tonkla-image-optimizer' ),
				IO_MIN_PHP_VERSION,
				PHP_VERSION
			)
		);
	}

	// Set default options
	io_set_default_options();

	// Migrate settings from theme if exists
	require_once IO_PLUGIN_DIR . 'includes/helpers/class-settings-migration.php';
	$migration = IO_Settings_Migration::get_instance();
	$migration->migrate_from_theme();

	// Clear update cache to prevent false update notifications
	io_clear_update_cache();

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'io_activate' );

/**
 * The code that runs during plugin deactivation.
 */
function io_deactivate() {
	// Clear transients
	io_clear_transients();

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'io_deactivate' );

/**
 * Set default plugin options
 */
function io_set_default_options() {
	$defaults = array(
		'io_image_auto_resize'        => '1',
		'io_image_max_width'          => 1920,
		'io_image_max_height'         => 1080,
		'io_image_maintain_aspect'    => '1',
		'io_image_jpeg_quality'       => 85,
		'io_image_max_file_size'      => 0,
		'io_image_webp_enabled'       => '1',
		'io_image_webp_quality'       => 85,
		'io_image_strip_exif'         => '1',
	);

	foreach ( $defaults as $option => $value ) {
		if ( get_option( $option ) === false ) {
			add_option( $option, $value );
		}
	}
}

/**
 * Migrate settings from theme if exists
 * This function is kept for backward compatibility
 * 
 * @deprecated Use IO_Settings_Migration class instead
 */
function io_migrate_theme_settings() {
	require_once IO_PLUGIN_DIR . 'includes/helpers/class-settings-migration.php';
	$migration = IO_Settings_Migration::get_instance();
	return $migration->migrate_from_theme();
}

/**
 * Clear all plugin transients
 */
function io_clear_transients() {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_io_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_io_' ) . '%'
		)
	);
}

/**
 * Autoloader for plugin classes
 *
 * @param string $class_name Class name to load.
 */
function io_autoloader( $class_name ) {
	// Only load our classes
	if ( strpos( $class_name, 'IO_' ) !== 0 ) {
		return;
	}

	// Convert class name to file name
	$class_file = str_replace( 'IO_', '', $class_name );
	$class_file = str_replace( '_', '-', strtolower( $class_file ) );

	// Possible file paths
	$paths = array(
		IO_PLUGIN_DIR . 'includes/class-' . $class_file . '.php',
		IO_PLUGIN_DIR . 'includes/admin/class-' . $class_file . '.php',
		IO_PLUGIN_DIR . 'includes/ajax/class-' . $class_file . '.php',
		IO_PLUGIN_DIR . 'includes/helpers/class-' . $class_file . '.php',
	);

	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			break;
		}
	}
}
spl_autoload_register( 'io_autoloader' );

/**
 * Load helper functions
 */
require_once IO_PLUGIN_DIR . 'includes/helpers/functions.php';

/**
 * Clear WordPress update cache
 * This prevents WordPress from checking updates from WordPress.org
 */
function io_clear_update_cache() {
	global $wpdb;
	
	$plugin_file = IO_PLUGIN_BASENAME;
	
	// Clear site transient for plugin updates
	delete_site_transient( 'update_plugins' );
	
	// Clear all update-related transients
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_site_transient_update_plugins' ) . '%',
			$wpdb->esc_like( '_site_transient_timeout_update_plugins' ) . '%'
		)
	);
	
	// Clear update cache for this specific plugin
	$transient_key = 'update_plugin_' . md5( $plugin_file );
	delete_site_transient( $transient_key );
	
	// Clear object cache
	wp_cache_delete( 'update_plugins', 'site-transient' );
	wp_cache_delete( 'alloptions', 'options' );
	
	// Force WordPress to re-check updates
	wp_cache_flush();
}

/**
 * Load settings migration (for backward compatibility)
 */
require_once IO_PLUGIN_DIR . 'includes/helpers/class-settings-migration.php';
IO_Settings_Migration::get_instance();

/**
 * Clear update cache immediately when plugin loads
 * This ensures the cache is cleared before WordPress checks for updates
 */
io_clear_update_cache();

/**
 * Prevent WordPress from checking updates from WordPress.org
 * This prevents conflicts with other plugins with similar names
 */
add_filter( 'site_transient_update_plugins', 'io_prevent_wp_org_update_check', 10, 1 );
function io_prevent_wp_org_update_check( $value ) {
	if ( ! is_object( $value ) ) {
		return $value;
	}
	
	$plugin_file = IO_PLUGIN_BASENAME;
	
	// Remove this plugin from update check if it exists
	if ( isset( $value->response ) && is_array( $value->response ) && isset( $value->response[ $plugin_file ] ) ) {
		unset( $value->response[ $plugin_file ] );
	}
	
	// Also remove from no_update array if exists
	if ( isset( $value->no_update ) && is_array( $value->no_update ) && isset( $value->no_update[ $plugin_file ] ) ) {
		unset( $value->no_update[ $plugin_file ] );
	}
	
	return $value;
}

/**
 * Prevent WordPress from including this plugin in update check requests
 */
add_filter( 'plugins_api', 'io_prevent_plugins_api_check', 10, 3 );
function io_prevent_plugins_api_check( $result, $action, $args ) {
	if ( isset( $args->slug ) && $args->slug === 'tonkla-image-optimizer' ) {
		// Return false to prevent WordPress from fetching plugin info from WordPress.org
		return false;
	}
	return $result;
}

/**
 * Remove this plugin from the list sent to WordPress.org for update checks
 */
add_filter( 'http_request_args', 'io_remove_from_update_check', 10, 2 );
function io_remove_from_update_check( $args, $url ) {
	// Only filter requests to WordPress.org plugin update API
	if ( strpos( $url, 'api.wordpress.org/plugins/update-check' ) === false ) {
		return $args;
	}
	
	// Parse the body to remove our plugin
	if ( isset( $args['body'] ) && is_string( $args['body'] ) ) {
		$body = json_decode( $args['body'], true );
		if ( is_array( $body ) && isset( $body['plugins'] ) && is_array( $body['plugins'] ) ) {
			$plugin_file = IO_PLUGIN_BASENAME;
			if ( isset( $body['plugins'][ $plugin_file ] ) ) {
				unset( $body['plugins'][ $plugin_file ] );
				$args['body'] = json_encode( $body );
			}
		}
	}
	
	return $args;
}

/**
 * Clear update cache on admin init (runs every time admin page loads)
 */
add_action( 'admin_init', 'io_clear_update_cache_on_admin_init', 1 );
function io_clear_update_cache_on_admin_init() {
	// Only clear on plugins page or update-core page
	$screen = get_current_screen();
	if ( $screen && ( $screen->id === 'plugins' || $screen->id === 'update-core' ) ) {
		io_clear_update_cache();
	}
}

/**
 * Initialize the plugin
 */
function io_init() {
	// Check WordPress version
	if ( version_compare( get_bloginfo( 'version' ), IO_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'io_wordpress_version_notice' );
		return;
	}

	// Check PHP version
	if ( version_compare( PHP_VERSION, IO_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'io_php_version_notice' );
		return;
	}

	// Phase 2 bridge: migrate io_image_* -> tonkla_img_* (idempotent, keeps old keys).
	if ( class_exists( 'Tonkla\\ImageOptimizer\\Settings' ) ) {
		\Tonkla\ImageOptimizer\Settings::migrate();
	}

	// Load admin classes
	if ( is_admin() ) {
		require_once IO_PLUGIN_DIR . 'includes/admin/class-admin.php';
		IO_Admin::get_instance();
	}

	// Load AJAX handlers
	require_once IO_PLUGIN_DIR . 'includes/ajax/class-ajax-handlers.php';
	IO_Ajax_Handlers::get_instance();

	// Load core classes
	require_once IO_PLUGIN_DIR . 'includes/class-image-optimizer.php';
	require_once IO_PLUGIN_DIR . 'includes/class-image-cleanup.php';
	require_once IO_PLUGIN_DIR . 'includes/class-webp-converter.php';

	// Initialize core functionality
	IO_Image_Optimizer::get_instance();
	IO_WebP_Converter::get_instance();
}
add_action( 'plugins_loaded', 'io_init' );

/**
 * Display WordPress version notice
 */
function io_wordpress_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: WordPress version, 2: Required WordPress version */
				esc_html__( 'Image Optimization requires WordPress %1$s or higher. You are running WordPress %2$s. Please upgrade and try again.', 'tonkla-image-optimizer' ),
				esc_html( IO_MIN_WP_VERSION ),
				esc_html( get_bloginfo( 'version' ) )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Display PHP version notice
 */
function io_php_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: PHP version, 2: Required PHP version */
				esc_html__( 'Image Optimization requires PHP %1$s or higher. You are running PHP %2$s. Please upgrade and try again.', 'tonkla-image-optimizer' ),
				esc_html( IO_MIN_PHP_VERSION ),
				esc_html( PHP_VERSION )
			);
			?>
		</p>
	</div>
	<?php
}
