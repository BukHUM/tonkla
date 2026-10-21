<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Image_Optimization
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall
 */
function io_uninstall() {
	// Delete plugin options
	$options = array(
		'io_image_auto_resize',
		'io_image_max_width',
		'io_image_max_height',
		'io_image_maintain_aspect',
		'io_image_jpeg_quality',
		'io_image_max_file_size',
		'io_image_webp_enabled',
		'io_image_webp_quality',
		'io_image_strip_exif',
		'io_settings_migrated',
		'io_settings_migrated_at',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete transients
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_io_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_io_' ) . '%'
		)
	);

	// Delete scan results transient
	delete_transient( 'io_scan_results' );
	delete_transient( 'trendtoday_scan_results' ); // Legacy

	// Note: We do NOT delete:
	// - Optimized images (user content)
	// - WebP files (user content)
	// - Attachment metadata
	// - Post meta (_webp_file)
}

// Run uninstall
io_uninstall();
