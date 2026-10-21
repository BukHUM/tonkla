<?php
/**
 * Helper Functions
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin version
 *
 * @return string Plugin version.
 */
function io_get_version() {
	return IO_VERSION;
}

/**
 * Get plugin directory path
 *
 * @return string Plugin directory path.
 */
function io_get_plugin_dir() {
	return IO_PLUGIN_DIR;
}

/**
 * Get plugin directory URL
 *
 * @return string Plugin directory URL.
 */
function io_get_plugin_url() {
	return IO_PLUGIN_URL;
}

/**
 * Get plugin basename
 *
 * @return string Plugin basename.
 */
function io_get_plugin_basename() {
	return IO_PLUGIN_BASENAME;
}

/**
 * Get plugin file path
 *
 * @return string Plugin file path.
 */
function io_get_plugin_file() {
	return IO_PLUGIN_FILE;
}

/**
 * Check if we're on the plugin settings page
 *
 * @return bool True if on settings page.
 */
function io_is_settings_page() {
	if ( ! is_admin() ) {
		return false;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return false;
	}

	return 'settings_page_tonkla-image-optimizer' === $screen->id;
}

/**
 * Get directory size
 *
 * @param string $directory Directory path.
 * @return int Directory size in bytes.
 */
function io_get_directory_size( $directory ) {
	$size = 0;

	if ( ! is_dir( $directory ) ) {
		return $size;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $iterator as $file ) {
		if ( $file->isFile() ) {
			$size += $file->getSize();
		}
	}

	return $size;
}

/**
 * Helper function: Check if WebP is supported
 *
 * @return bool True if WebP is supported.
 */
function io_webp_supported() {
	$converter = IO_WebP_Converter::get_instance();
	return $converter->webp_supported();
}

/**
 * Helper function: Get WebP URL for attachment
 *
 * @param string $url Original image URL.
 * @param int    $attachment_id Attachment ID.
 * @return string WebP URL or original URL.
 */
function io_get_webp_url( $url, $attachment_id = 0 ) {
	$converter = IO_WebP_Converter::get_instance();
	return $converter->get_webp_url( $url, $attachment_id );
}

/**
 * Helper function: Scan for unused images
 *
 * @param array $options Scan options.
 * @return array Scan results.
 */
function io_scan_unused_images( $options = array() ) {
	$cleanup = new IO_Image_Cleanup();
	return $cleanup->scan_unused_images( $options );
}

/**
 * Helper function: Get attachment thumbnails
 *
 * @param int $attachment_id Attachment ID.
 * @return array Thumbnail file paths.
 */
function io_get_attachment_thumbnails( $attachment_id ) {
	$cleanup = new IO_Image_Cleanup();
	return $cleanup->get_attachment_thumbnails( $attachment_id );
}

/**
 * Helper function: Find unused thumbnails
 *
 * @param array $options Scan options.
 * @return array Unused thumbnail files.
 */
function io_find_unused_thumbnails( $options = array() ) {
	$cleanup = new IO_Image_Cleanup();
	return $cleanup->scan_unused_thumbnails( $options );
}

/**
 * Helper function: Find unused WebP files
 *
 * @param array $options Scan options.
 * @return array Unused WebP files.
 */
function io_find_unused_webp( $options = array() ) {
	$cleanup = new IO_Image_Cleanup();
	return $cleanup->scan_unused_webp( $options );
}

/**
 * Helper function: Find orphaned images
 *
 * @param array $options Scan options.
 * @return array Orphaned image files.
 */
function io_find_orphaned_images( $options = array() ) {
	$cleanup = new IO_Image_Cleanup();
	return $cleanup->scan_orphaned_images( $options );
}

/**
 * Helper function: Delete unused images
 *
 * @param array $file_paths Files to delete.
 * @param array $options    Delete options.
 * @return array Results.
 */
function io_delete_unused_images( $file_paths, $options = array() ) {
	$cleanup = new IO_Image_Cleanup();
	$results = $cleanup->delete_unused_images( $file_paths, $options );

	// Clear cache after deletion
	if ( isset( $results['deleted'] ) && $results['deleted'] > 0 ) {
		$cleanup->clear_thumbnails_cache();
	}

	return $results;
}

/**
 * Helper function: Clear image cleanup cache
 *
 * @return void
 */
function io_clear_image_cleanup_cache() {
	$cleanup = new IO_Image_Cleanup();
	$cleanup->clear_thumbnails_cache();

	// Also clear transient
	delete_transient( 'io_scan_results' );
	delete_transient( 'trendtoday_scan_results' ); // Legacy
}
