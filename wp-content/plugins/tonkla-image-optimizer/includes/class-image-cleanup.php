<?php
/**
 * Image Cleanup Class
 * Scan and delete unused images, thumbnails, and WebP files
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Cleanup Class
 */
class IO_Image_Cleanup {

	/**
	 * Upload directory path
	 *
	 * @var string
	 */
	private $uploads_dir;

	/**
	 * Upload directory URL
	 *
	 * @var string
	 */
	private $uploads_url;

	/**
	 * Valid image extensions
	 *
	 * @var array
	 */
	private $image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico' );

	/**
	 * Protected files/folders (whitelist)
	 *
	 * @var array
	 */
	private $protected_files = array(
		'.htaccess',
		'index.php',
		'.htpasswd',
	);

	/**
	 * Protected directories (whitelist)
	 *
	 * @var array
	 */
	private $protected_dirs = array(
		'backups',
		'upgrade',
	);

	/**
	 * Known option / theme_mod keys that store site images (attachment IDs).
	 *
	 * @var array
	 */
	private $protected_option_keys = array(
		'site_icon',
		'main_logo',
		'_main_logo',
		'footer_logo',
		'_footer_logo',
		'ad_sidebar_image',
		'_ad_sidebar_image',
	);

	/**
	 * Cached protected file paths (normalized keys).
	 *
	 * @var array|null
	 */
	private $protected_paths_cache = null;

	/**
	 * Count of files skipped because they are protected (last scan).
	 *
	 * @var int
	 */
	private $protected_skipped_count = 0;

	/**
	 * Constructor
	 */
	public function __construct() {
		$upload_dir        = wp_upload_dir();
		$this->uploads_dir = $upload_dir['basedir'];
		$this->uploads_url = $upload_dir['baseurl'];
	}

	/**
	 * Normalize a filesystem path for consistent map lookups.
	 *
	 * @param string $path File path.
	 * @return string Normalized path or empty string if invalid.
	 */
	public function normalize_path( $path ) {
		if ( empty( $path ) || ! is_string( $path ) ) {
			return '';
		}

		$path = str_replace( '\\', '/', $path );
		$path = wp_normalize_path( $path );

		$real = realpath( $path );
		if ( $real ) {
			$path = wp_normalize_path( str_replace( '\\', '/', $real ) );
		}

		// Windows paths are case-insensitive — lowercase for stable keys.
		if ( 0 === stripos( PHP_OS, 'WIN' ) ) {
			$path = strtolower( $path );
		}

		return $path;
	}

	/**
	 * Build a public URL for a file under uploads.
	 *
	 * @param string $absolute_path Absolute filesystem path.
	 * @return string URL or empty string.
	 */
	private function path_to_url( $absolute_path ) {
		$rel = $this->to_uploads_relative( $absolute_path );
		if ( ! $rel ) {
			return '';
		}
		// Preserve URL case from relative segments (lowercase only on Windows lookup keys).
		$slash = str_replace( '\\', '/', (string) $absolute_path );
		$base  = str_replace( '\\', '/', (string) $this->uploads_dir );
		if ( 0 === stripos( $slash, $base ) ) {
			$display_rel = ltrim( substr( $slash, strlen( $base ) ), '/' );
			return trailingslashit( $this->uploads_url ) . str_replace( '\\', '/', $display_rel );
		}
		return trailingslashit( $this->uploads_url ) . str_replace( '\\', '/', $rel );
	}

	/**
	 * Convert absolute path to uploads-relative key (e.g. 2024/10/foo.jpg).
	 * Prefer this for lookups — avoids Windows absolute-path mismatches.
	 *
	 * @param string $path Absolute or relative path.
	 * @return string Relative key or empty string.
	 */
	public function to_uploads_relative( $path ) {
		if ( empty( $path ) || ! is_string( $path ) ) {
			return '';
		}

		// Already relative (WordPress _wp_attached_file style).
		$slash_path = str_replace( '\\', '/', $path );
		if ( ! preg_match( '#^[a-z]:/#i', $slash_path ) && 0 !== strpos( $slash_path, '/' ) ) {
			$rel = ltrim( $slash_path, '/' );
			return ( 0 === stripos( PHP_OS, 'WIN' ) ) ? strtolower( $rel ) : $rel;
		}

		$norm = $this->normalize_path( $path );
		$base = $this->normalize_path( $this->uploads_dir );

		if ( $norm && $base && 0 === strpos( $norm, $base ) ) {
			return ltrim( substr( $norm, strlen( $base ) ), '/' );
		}

		// Fallback without realpath (file missing but meta still valid).
		$uploads = str_replace( '\\', '/', wp_normalize_path( $this->uploads_dir ) );
		$candidate = str_replace( '\\', '/', wp_normalize_path( $path ) );
		if ( 0 === stripos( PHP_OS, 'WIN' ) ) {
			$uploads   = strtolower( $uploads );
			$candidate = strtolower( $candidate );
		}
		if ( 0 === strpos( $candidate, $uploads ) ) {
			return ltrim( substr( $candidate, strlen( $uploads ) ), '/' );
		}

		return '';
	}

	/**
	 * Query all image attachment IDs across languages (Polylang-safe).
	 *
	 * Polylang filters get_posts() to the current language by default, which
	 * makes media from other languages look "orphaned". Pass lang => '' for all.
	 *
	 * @return int[] Attachment IDs.
	 */
	private function query_all_image_attachment_ids() {
		$args = array(
			'post_type'              => 'attachment',
			'post_mime_type'         => 'image',
			'posts_per_page'         => -1,
			'post_status'            => 'inherit',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'lang'                   => '', // Polylang: all languages.
		);

		/**
		 * Filter attachment query args used by Image Optimization cleanup.
		 *
		 * @param array $args WP_Query args.
		 */
		$args = apply_filters( 'io_cleanup_attachment_query_args', $args );

		$ids = get_posts( $args );
		if ( is_wp_error( $ids ) || ! is_array( $ids ) ) {
			return array();
		}

		return array_map( 'absint', $ids );
	}

	/**
	 * Register both absolute and relative keys for a file path in a lookup map.
	 *
	 * @param array  $map  Map by reference.
	 * @param string $path File path.
	 * @return void
	 */
	private function register_lookup_keys( array &$map, $path ) {
		if ( empty( $path ) || ! is_string( $path ) ) {
			return;
		}

		// Skip corrupted absolute paths from migrated installs (e.g. C:xampphtdocs...).
		$slash = str_replace( '\\', '/', $path );
		if ( preg_match( '#^[a-z]:[^/]#i', $slash ) && ! preg_match( '#^[a-z]:/#i', $slash ) ) {
			return;
		}

		$abs = $this->normalize_path( $path );
		if ( $abs ) {
			$map[ $abs ] = true;
		}
		$rel = $this->to_uploads_relative( $path );
		if ( $rel ) {
			$map[ $rel ] = true;
		}
	}

	/**
	 * Whether a path exists in a lookup map (absolute or relative key).
	 *
	 * @param string $path File path.
	 * @param array  $map  Lookup map.
	 * @return bool
	 */
	private function is_in_lookup_map( $path, array $map ) {
		$abs = $this->normalize_path( $path );
		if ( $abs && isset( $map[ $abs ] ) ) {
			return true;
		}
		$rel = $this->to_uploads_relative( $path );
		if ( $rel && isset( $map[ $rel ] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Branding filenames that must never be treated as orphaned/unused.
	 *
	 * @param string $filename File name only.
	 * @return bool
	 */
	private function is_protected_filename( $filename ) {
		$name = strtolower( (string) $filename );
		if ( '' === $name ) {
			return false;
		}

		// favicon.png, favicon.webp, favicon-32x32.png, favicon.ico
		if ( preg_match( '/^favicon(\.|[-_])/', $name ) || 'favicon.ico' === $name ) {
			return true;
		}

		// site-icon-32x32.png (WP Site Icon crops)
		if ( preg_match( '/^site[-_]?icon/', $name ) ) {
			return true;
		}

		// small-logo-190.png, logo.webp, main-logo-150x150.png
		if ( preg_match( '/(^|[-_])logo([-_\.]|$)/', $name ) ) {
			return true;
		}

		return (bool) apply_filters( 'io_is_protected_filename', false, $filename );
	}

	/**
	 * Check whether a path is in a protected set (O(1) lookup).
	 *
	 * @param string $path File path.
	 * @param array  $protected_paths Map of normalized path => true.
	 * @return bool
	 */
	private function is_path_protected( $path, $protected_paths = null ) {
		if ( null === $protected_paths ) {
			$protected_paths = $this->get_protected_file_paths();
		}
		if ( $this->is_in_lookup_map( $path, $protected_paths ) ) {
			return true;
		}
		return $this->is_protected_filename( basename( $path ) );
	}

	/**
	 * Collect attachment IDs that must never be deleted (site branding, etc.).
	 *
	 * @return array List of unique attachment IDs.
	 */
	public function get_protected_attachment_ids() {
		$ids = array();

		$site_icon = absint( get_option( 'site_icon' ) );
		if ( $site_icon > 0 ) {
			$ids[] = $site_icon;
		}

		$custom_logo = absint( get_theme_mod( 'custom_logo' ) );
		if ( $custom_logo > 0 ) {
			$ids[] = $custom_logo;
		}

		if ( function_exists( 'get_custom_header' ) ) {
			$header = get_custom_header();
			if ( ! empty( $header->attachment_id ) ) {
				$ids[] = absint( $header->attachment_id );
			}
		}

		foreach ( $this->protected_option_keys as $key ) {
			$value = get_option( $key );
			$id    = $this->extract_attachment_id( $value );
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		// Carbon Fields theme options (if available).
		if ( function_exists( 'carbon_get_theme_option' ) ) {
			$cf_fields = array( 'main_logo', 'footer_logo', 'ad_sidebar_image' );
			foreach ( $cf_fields as $field ) {
				$id = $this->extract_attachment_id( carbon_get_theme_option( $field ) );
				if ( $id > 0 ) {
					$ids[] = $id;
				}
			}
		}

		// Theme mods: numeric values that look like attachment IDs.
		$theme_mods = get_theme_mods();
		if ( is_array( $theme_mods ) ) {
			foreach ( $theme_mods as $mod_value ) {
				$id = $this->extract_attachment_id( $mod_value );
				if ( $id > 0 && wp_attachment_is_image( $id ) ) {
					$ids[] = $id;
				}
			}
		}

		/**
		 * Filter protected attachment IDs for Image Optimization cleanup.
		 *
		 * @param array $ids Attachment IDs.
		 */
		$ids = apply_filters( 'tonkla_img_protected_attachment_ids', array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		$ids = apply_filters( 'io_protected_attachment_ids', $ids );

		return array_values( $ids );
	}

	/**
	 * Extract an attachment ID from option / Carbon field value.
	 *
	 * @param mixed $value Raw value.
	 * @return int Attachment ID or 0.
	 */
	private function extract_attachment_id( $value ) {
		if ( is_numeric( $value ) ) {
			return absint( $value );
		}
		if ( is_array( $value ) ) {
			if ( isset( $value['id'] ) ) {
				return absint( $value['id'] );
			}
			if ( isset( $value[0] ) && is_numeric( $value[0] ) ) {
				return absint( $value[0] );
			}
		}
		if ( is_string( $value ) && '' !== $value ) {
			// URL pointing into uploads → resolve to attachment.
			if ( 0 === strpos( $value, 'http' ) || 0 === strpos( $value, '/' ) ) {
				$id = attachment_url_to_postid( $value );
				return $id ? absint( $id ) : 0;
			}
		}
		return 0;
	}

	/**
	 * Build map of protected file paths (original + sizes + WebP).
	 *
	 * @param bool $use_cache Whether to use instance cache.
	 * @return array Map of normalized path => true.
	 */
	public function get_protected_file_paths( $use_cache = true ) {
		if ( $use_cache && null !== $this->protected_paths_cache ) {
			return $this->protected_paths_cache;
		}

		$paths = array();
		$ids   = $this->get_protected_attachment_ids();

		foreach ( $ids as $attachment_id ) {
			$thumbnails = $this->get_attachment_thumbnails( $attachment_id );
			foreach ( $thumbnails as $path => $value ) {
				$this->register_lookup_keys( $paths, $path );
			}

			// Also protect by URL → path if file moved but meta still valid.
			$url = wp_get_attachment_url( $attachment_id );
			if ( $url ) {
				$upload_dir    = wp_upload_dir();
				$file_from_url = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
				$this->register_lookup_keys( $paths, $file_from_url );
			}
		}

		/**
		 * Filter protected file paths for cleanup.
		 *
		 * @param array $paths Normalized path map.
		 */
		$paths = apply_filters( 'tonkla_img_protected_file_paths', $paths );
		$paths = apply_filters( 'io_protected_file_paths', $paths );

		$this->protected_paths_cache = $paths;
		return $paths;
	}

	/**
	 * Filter a scan result file list, removing protected paths.
	 *
	 * @param array $files List of file info arrays with 'path' key.
	 * @return array Filtered list.
	 */
	private function filter_protected_from_results( $files ) {
		if ( empty( $files ) || ! is_array( $files ) ) {
			return array();
		}

		$protected = $this->get_protected_file_paths();
		$filtered  = array();

		foreach ( $files as $file ) {
			$path = isset( $file['path'] ) ? $file['path'] : '';
			if ( $this->is_path_protected( $path, $protected ) ) {
				$this->protected_skipped_count++;
				continue;
			}
			$filtered[] = $file;
		}

		return $filtered;
	}

	/**
	 * Log deletion activity
	 *
	 * @param string $action Action performed.
	 * @param array  $data   Data to log.
	 * @return void
	 */
	private function log_action( $action, $data = array() ) {
		if ( ! function_exists( 'error_log' ) ) {
			return;
		}

		$log_message = sprintf(
			'[Image Optimization] %s: %s',
			$action,
			wp_json_encode( $data )
		);

		error_log( $log_message );
	}

	/**
	 * Get all registered thumbnails for an attachment
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Array of thumbnail file paths.
	 */
	public function get_attachment_thumbnails( $attachment_id ) {
		$thumbnails = array();

		// Prefer relative path from meta (stable across OS / realpath quirks).
		$attached_rel = get_post_meta( $attachment_id, '_wp_attached_file', true );
		if ( is_string( $attached_rel ) && '' !== $attached_rel ) {
			$this->register_lookup_keys( $thumbnails, $attached_rel );
			$abs_from_rel = path_join( $this->uploads_dir, $attached_rel );
			$this->register_lookup_keys( $thumbnails, $abs_from_rel );

			// Sibling WebP of original.
			$webp_rel = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $attached_rel );
			if ( $webp_rel && $webp_rel !== $attached_rel ) {
				$this->register_lookup_keys( $thumbnails, $webp_rel );
				$this->register_lookup_keys( $thumbnails, path_join( $this->uploads_dir, $webp_rel ) );
			}
		}

		$file = get_attached_file( $attachment_id );
		if ( $file ) {
			$this->register_lookup_keys( $thumbnails, $file );
			$webp_abs = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file );
			if ( $webp_abs && $webp_abs !== $file ) {
				$this->register_lookup_keys( $thumbnails, $webp_abs );
			}
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! $metadata || empty( $metadata['sizes'] ) || ! is_array( $metadata['sizes'] ) ) {
			$webp_file = \Tonkla\ImageOptimizer\WebP_Converter::get_webp_file( (int) $attachment_id );
			if ( $webp_file && is_string( $webp_file ) && file_exists( $webp_file ) ) {
				$this->register_lookup_keys( $thumbnails, $webp_file );
			}
			return $thumbnails;
		}

		$file_dir = $file ? dirname( $file ) : '';
		$rel_dir  = ( is_string( $attached_rel ) && '' !== $attached_rel ) ? dirname( $attached_rel ) : '';
		if ( '.' === $rel_dir ) {
			$rel_dir = '';
		}

		foreach ( $metadata['sizes'] as $size_data ) {
			if ( empty( $size_data['file'] ) ) {
				continue;
			}
			$size_file = $size_data['file'];
			if ( $file_dir ) {
				$this->register_lookup_keys( $thumbnails, path_join( $file_dir, $size_file ) );
			}
			if ( '' !== $rel_dir ) {
				$this->register_lookup_keys( $thumbnails, path_join( $rel_dir, $size_file ) );
			} else {
				$this->register_lookup_keys( $thumbnails, $size_file );
			}
			// WebP sibling of each size.
			$size_webp = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $size_file );
			if ( $size_webp && $size_webp !== $size_file ) {
				if ( $file_dir ) {
					$this->register_lookup_keys( $thumbnails, path_join( $file_dir, $size_webp ) );
				}
				if ( '' !== $rel_dir ) {
					$this->register_lookup_keys( $thumbnails, path_join( $rel_dir, $size_webp ) );
				} else {
					$this->register_lookup_keys( $thumbnails, $size_webp );
				}
			}
		}

		$webp_file = \Tonkla\ImageOptimizer\WebP_Converter::get_webp_file( (int) $attachment_id );
		// Only register if the meta path still exists (skip broken migrated paths).
		if ( $webp_file && is_string( $webp_file ) && file_exists( $webp_file ) ) {
			$this->register_lookup_keys( $thumbnails, $webp_file );
		}

		return $thumbnails;
	}

	/**
	 * Get all registered thumbnails for all attachments
	 *
	 * @param bool $use_cache Whether to use cached results.
	 * @return array Array of all registered thumbnail file paths (keys only for O(1) lookup).
	 */
	public function get_all_registered_thumbnails( $use_cache = true ) {
		$cache_key = 'io_registered_thumbnails';

		// Try to get from cache
		if ( $use_cache ) {
			$cached = wp_cache_get( $cache_key, 'io_image_cleanup' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$registered_files = array();

		try {
			// All languages (Polylang) — otherwise media in other langs looks orphaned.
			$attachments = $this->query_all_image_attachment_ids();

		// Process in batches to avoid memory issues
		$batch_size = 100;
		$total      = count( $attachments );

		for ( $i = 0; $i < $total; $i += $batch_size ) {
			$batch = array_slice( $attachments, $i, $batch_size );

			foreach ( $batch as $attachment_id ) {
				$thumbnails        = $this->get_attachment_thumbnails( $attachment_id );
				$registered_files = array_merge( $registered_files, $thumbnails );
			}

			// Clear memory periodically
			if ( $i % ( $batch_size * 10 ) === 0 ) {
				wp_cache_flush_group( 'io_image_cleanup' );
			}
		}

			// Keys from get_attachment_thumbnails already include abs + relative lookups.
			$normalized = $registered_files;

			// Merge protected site assets so they never appear as unused
			foreach ( $this->get_protected_file_paths() as $path => $flag ) {
				$this->register_lookup_keys( $normalized, $path );
			}

			// Cache for 5 minutes
			wp_cache_set( $cache_key, $normalized, 'io_image_cleanup', 300 );

			return $normalized;
		} catch ( Exception $e ) {
			$this->log_action(
				'GET_REGISTERED_THUMBNAILS_ERROR',
				array(
					'error' => $e->getMessage(),
					'trace' => $e->getTraceAsString(),
				)
			);
			throw $e; // Re-throw to be handled by caller
		}
	}

	/**
	 * Clear cache for registered thumbnails
	 *
	 * @return void
	 */
	public function clear_thumbnails_cache() {
		wp_cache_delete( 'io_registered_thumbnails', 'io_image_cleanup' );
		$this->protected_paths_cache = null;
	}

	/**
	 * Check if file is a thumbnail by filename pattern
	 *
	 * @param string $filename File name.
	 * @return bool True if looks like thumbnail.
	 */
	private function is_thumbnail_filename( $filename ) {
		// WordPress thumbnail pattern: filename-150x150.jpg
		if ( preg_match( '/-\d+x\d+\.(jpg|jpeg|png|gif|webp)$/i', $filename ) ) {
			return true;
		}

		// Other common patterns
		if ( preg_match( '/_(thumbnail|thumb|small|medium|large)\./i', $filename ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Find unused thumbnails
	 *
	 * @param array $options Scan options.
	 * @return array Array of unused thumbnail files with details.
	 */
	public function scan_unused_thumbnails( $options = array() ) {
		$unused_thumbnails = array();
		$error_files = array();

		// Get all registered thumbnails
		try {
			$registered_files = $this->get_all_registered_thumbnails( isset( $options['use_cache'] ) ? $options['use_cache'] : true );
		} catch ( Exception $e ) {
			$error_files[] = array(
				'file'  => __( 'การดึงข้อมูล registered thumbnails', 'tonkla-image-optimizer' ),
				'error' => $e->getMessage(),
			);
			$registered_files = array(); // Continue with empty array
		}

		// Scan uploads directory
		if ( ! is_dir( $this->uploads_dir ) ) {
			$this->log_action(
				'SCAN_ERROR',
				array(
					'type'  => 'thumbnails',
					'error' => 'Uploads directory not found',
				)
			);
			return array(
				'files' => $unused_thumbnails,
				'errors' => array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่พบโฟลเดอร์ uploads', 'tonkla-image-optimizer' ),
					)
				),
			);
		}
		
		if ( ! is_readable( $this->uploads_dir ) ) {
			return array(
				'files' => $unused_thumbnails,
				'errors' => array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถอ่านโฟลเดอร์ uploads ได้', 'tonkla-image-optimizer' ),
					)
				),
			);
		}

		$max_files = isset( $options['max_files'] ) ? absint( $options['max_files'] ) : 0;
		$processed = 0;
		$total_scanned = 0;
		$memory_check_interval = 100; // Check memory every 100 files

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $this->uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);
		} catch ( Exception $e ) {
			return array(
				'files' => $unused_thumbnails,
				'errors' => array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถเข้าถึงโฟลเดอร์ uploads: ', 'tonkla-image-optimizer' ) . $e->getMessage(),
					)
				),
			);
		}

		foreach ( $iterator as $file ) {
			// Check max files limit
			if ( $max_files > 0 && $processed >= $max_files ) {
				break;
			}

			if ( $file->isDir() ) {
				continue;
			}

			$total_scanned++;
			$file_path = null;
			$filename = '';

			// Memory management: Check and clean up every N files
			if ( $total_scanned % $memory_check_interval === 0 ) {
				// Get current memory usage
				$memory_used = memory_get_usage( true );
				$memory_limit = ini_get( 'memory_limit' );
				$memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
				$memory_percent = ( $memory_used / $memory_limit_bytes ) * 100;
				
				// If memory usage is above 80%, trigger garbage collection
				if ( $memory_percent > 80 ) {
					// Unset large variables that are no longer needed
					unset( $file_path, $filename );
					
					// Force garbage collection
					if ( function_exists( 'gc_collect_cycles' ) ) {
						gc_collect_cycles();
					}
					
					// Log memory warning
					$this->log_action(
						'SCAN_MEMORY_WARNING',
						array(
							'memory_used' => $this->format_bytes( $memory_used ),
							'memory_limit' => $memory_limit,
							'percent' => round( $memory_percent, 2 ),
							'files_scanned' => $total_scanned,
						)
					);
				}
			}

			try {
				$raw_path = $file->getRealPath();
				if ( ! $raw_path ) {
					$error_files[] = array(
						'file'  => $file->getPathname(),
						'error' => __( 'ไม่สามารถอ่าน path ของไฟล์ได้', 'tonkla-image-optimizer' ),
					);
					continue;
				}
				
				$file_path = $this->normalize_path( $raw_path );
				if ( ! $file_path ) {
					continue;
				}

				$filename = $file->getFilename();

				// Skip if not an image
				$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, $this->image_extensions, true ) ) {
					continue;
				}

				// Skip if not a thumbnail pattern
				if ( ! $this->is_thumbnail_filename( $filename ) ) {
					continue;
				}

				// Skip if file is registered or protected (logo, favicon, etc.)
				$is_registered = $this->is_in_lookup_map( $file_path, $registered_files );
				$is_protected  = $this->is_path_protected( $file_path );
				if ( $is_registered || $is_protected ) {
					if ( $is_protected && ! $is_registered ) {
						$this->protected_skipped_count++;
					}
					continue;
				}

				// Add to unused list
				$unused_thumbnails[] = array(
					'path'     => $file_path,
					'filename' => $filename,
					'size'     => $file->getSize(),
					'modified' => $file->getMTime(),
					'url'      => $this->path_to_url( $raw_path ),
				);

				$processed++;
			} catch ( Exception $e ) {
				// Log error but continue
				$this->log_action(
					'SCAN_FILE_ERROR',
					array(
						'file'  => $file->getPathname(),
						'error' => $e->getMessage(),
					)
				);
				$error_files[] = array(
					'file'  => $file_path ? $file_path : $file->getPathname(),
					'filename' => $filename ? $filename : basename( $file->getPathname() ),
					'error' => $e->getMessage(),
				);
				continue;
			}
		}

		return array(
			'files' => $unused_thumbnails,
			'errors' => $error_files,
			'statistics' => array(
				'total_scanned' => $total_scanned,
				'processed' => $processed,
			),
		);
	}

	/**
	 * Find unused WebP files
	 *
	 * @param array $options Scan options.
	 * @return array Array of unused WebP files with details.
	 */
	public function scan_unused_webp( $options = array() ) {
		$unused_webp = array();
		$error_files = array();

		$attachments = $this->query_all_image_attachment_ids();

		// Valid WebP = any file belonging to an attachment (incl. sibling .webp) + protected assets.
		$valid_webp = array();
		foreach ( $attachments as $attachment_id ) {
			foreach ( $this->get_attachment_thumbnails( $attachment_id ) as $path => $flag ) {
				$this->register_lookup_keys( $valid_webp, $path );
			}
		}
		foreach ( $this->get_protected_file_paths() as $protected_path => $flag ) {
			$this->register_lookup_keys( $valid_webp, $protected_path );
		}

		// Scan for all WebP files
		if ( ! is_dir( $this->uploads_dir ) ) {
			return array(
				'files' => $unused_webp,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่พบโฟลเดอร์ uploads', 'tonkla-image-optimizer' ),
					)
				) ),
			);
		}
		
		if ( ! is_readable( $this->uploads_dir ) ) {
			return array(
				'files' => $unused_webp,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถอ่านโฟลเดอร์ uploads ได้', 'tonkla-image-optimizer' ),
					)
				) ),
			);
		}

		$total_scanned = 0;
		$memory_check_interval = 100; // Check memory every 100 files
		
		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $this->uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);
		} catch ( Exception $e ) {
			return array(
				'files' => $unused_webp,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถเข้าถึงโฟลเดอร์ uploads: ', 'tonkla-image-optimizer' ) . $e->getMessage(),
					)
				) ),
			);
		}

		foreach ( $iterator as $file ) {
			if ( $file->isDir() ) {
				continue;
			}

			$total_scanned++;
			$file_path = null;
			$filename = '';

			// Memory management: Check and clean up every N files
			if ( $total_scanned % $memory_check_interval === 0 ) {
				$memory_used = memory_get_usage( true );
				$memory_limit = ini_get( 'memory_limit' );
				$memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
				$memory_percent = $memory_limit_bytes > 0 ? ( $memory_used / $memory_limit_bytes ) * 100 : 0;
				
				if ( $memory_percent > 80 ) {
					unset( $file_path, $filename );
					if ( function_exists( 'gc_collect_cycles' ) ) {
						gc_collect_cycles();
					}
				}
			}

			try {
				$raw_path = $file->getRealPath();
				if ( ! $raw_path ) {
					continue;
				}
				$file_path = $this->normalize_path( $raw_path );
				if ( ! $file_path ) {
					continue;
				}
				$filename = $file->getFilename();

				// Only process WebP files
				if ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) !== 'webp' ) {
					continue;
				}

				// Skip if valid WebP or protected site asset
				$is_valid_webp = $this->is_in_lookup_map( $file_path, $valid_webp );
				$is_protected  = $this->is_path_protected( $file_path );
				if ( $is_valid_webp || $is_protected ) {
					if ( $is_protected && ! $is_valid_webp ) {
						$this->protected_skipped_count++;
					}
					continue;
				}

				// Check if original file exists (use raw path for filesystem ops)
				$original_file = preg_replace( '/\.webp$/i', '.jpg', $raw_path );
				if ( ! file_exists( $original_file ) ) {
					$original_file = preg_replace( '/\.webp$/i', '.jpeg', $raw_path );
				}
				if ( ! file_exists( $original_file ) ) {
					$original_file = preg_replace( '/\.webp$/i', '.png', $raw_path );
				}

				// If no original exists, it's unused
				if ( ! file_exists( $original_file ) ) {
					$unused_webp[] = array(
						'path'     => $file_path,
						'filename' => $filename,
						'size'     => $file->getSize(),
						'modified' => $file->getMTime(),
						'url'      => $this->path_to_url( $raw_path ),
					);
				}
			} catch ( Exception $e ) {
				$error_files[] = array(
					'file'  => $file_path ? $file_path : $file->getPathname(),
					'filename' => $filename ? $filename : basename( $file->getPathname() ),
					'error' => $e->getMessage(),
				);
				continue;
			}
		}

		return array(
			'files' => $unused_webp,
			'errors' => $error_files,
			'statistics' => array(
				'total_scanned' => $total_scanned,
			),
		);
	}

	/**
	 * Get all attachment file paths
	 *
	 * @return array Array of attachment file paths (keys only for O(1) lookup).
	 */
	private function get_all_attachment_files() {
		$attachment_files = array();

		$attachments = $this->query_all_image_attachment_ids();

		foreach ( $attachments as $attachment_id ) {
			$thumbnails = $this->get_attachment_thumbnails( $attachment_id );
			foreach ( $thumbnails as $thumb_path => $value ) {
				$this->register_lookup_keys( $attachment_files, $thumb_path );
			}
		}

		// Merge protected site assets (logo, favicon, Carbon Fields images)
		foreach ( $this->get_protected_file_paths() as $path => $flag ) {
			$this->register_lookup_keys( $attachment_files, $path );
		}

		return $attachment_files;
	}

	/**
	 * Find orphaned images (files without attachment record)
	 *
	 * @param array $options Scan options.
	 * @return array Array of orphaned image files with details.
	 */
	public function scan_orphaned_images( $options = array() ) {
		$orphaned_images = array();
		$error_files = array();

		// Get all attachment files
		$attachment_files = array();
		try {
			$attachment_files = $this->get_all_attachment_files();
		} catch ( Exception $e ) {
			$error_files[] = array(
				'file'  => __( 'การดึงข้อมูล attachment files', 'tonkla-image-optimizer' ),
				'error' => $e->getMessage(),
			);
		}

		// Scan uploads directory
		if ( ! is_dir( $this->uploads_dir ) ) {
			return array(
				'files' => $orphaned_images,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่พบโฟลเดอร์ uploads', 'tonkla-image-optimizer' ),
					)
				) ),
			);
		}
		
		if ( ! is_readable( $this->uploads_dir ) ) {
			return array(
				'files' => $orphaned_images,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถอ่านโฟลเดอร์ uploads ได้', 'tonkla-image-optimizer' ),
					)
				) ),
			);
		}

		$total_scanned = 0;
		$memory_check_interval = 100; // Check memory every 100 files
		
		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $this->uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);
		} catch ( Exception $e ) {
			return array(
				'files' => $orphaned_images,
				'errors' => array_merge( $error_files, array(
					array(
						'file'  => $this->uploads_dir,
						'error' => __( 'ไม่สามารถเข้าถึงโฟลเดอร์ uploads: ', 'tonkla-image-optimizer' ) . $e->getMessage(),
					)
				) ),
			);
		}

		foreach ( $iterator as $file ) {
			if ( $file->isDir() ) {
				continue;
			}

			$total_scanned++;
			$file_path = null;
			$filename = '';

			// Memory management: Check and clean up every N files
			if ( $total_scanned % $memory_check_interval === 0 ) {
				$memory_used = memory_get_usage( true );
				$memory_limit = ini_get( 'memory_limit' );
				$memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
				$memory_percent = $memory_limit_bytes > 0 ? ( $memory_used / $memory_limit_bytes ) * 100 : 0;
				
				if ( $memory_percent > 80 ) {
					unset( $file_path, $filename );
					if ( function_exists( 'gc_collect_cycles' ) ) {
						gc_collect_cycles();
					}
				}
			}

			try {
				$raw_path = $file->getRealPath();
				if ( ! $raw_path ) {
					continue;
				}
				$file_path = $this->normalize_path( $raw_path );
				if ( ! $file_path ) {
					continue;
				}
				$filename = $file->getFilename();

				// Skip if not an image
				$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, $this->image_extensions, true ) ) {
					continue;
				}

				// Skip if registered as attachment or protected site asset / branding filename
				$is_registered = $this->is_in_lookup_map( $file_path, $attachment_files );
				$is_protected  = $this->is_path_protected( $file_path );
				if ( $is_registered || $is_protected ) {
					if ( $is_protected && ! $is_registered ) {
						$this->protected_skipped_count++;
					}
					continue;
				}

				// Skip system files
				if ( in_array( $filename, array( '.htaccess', 'index.php' ), true ) ) {
					continue;
				}

				// Add to orphaned list
				$orphaned_images[] = array(
					'path'     => $file_path,
					'filename' => $filename,
					'size'     => $file->getSize(),
					'modified' => $file->getMTime(),
					'url'      => $this->path_to_url( $raw_path ),
				);
			} catch ( Exception $e ) {
				$error_files[] = array(
					'file'  => $file_path ? $file_path : $file->getPathname(),
					'filename' => $filename ? $filename : basename( $file->getPathname() ),
					'error' => $e->getMessage(),
				);
				continue;
			}
		}

		return array(
			'files' => $orphaned_images,
			'errors' => $error_files,
			'statistics' => array(
				'total_scanned' => $total_scanned,
			),
		);
	}

	/**
	 * Scan for all unused images
	 *
	 * @param array $options Scan options.
	 * @return array Complete scan results.
	 */
	public function scan_unused_images( $options = array() ) {
		$defaults = array(
			'use_cache'  => true,
			'scan_type'  => 'all', // 'all', 'thumbnails', 'webp', 'orphaned'
			'max_files'  => 0, // 0 = unlimited
			'time_limit' => 0, // 0 = unlimited (seconds)
		);
		$options = wp_parse_args( $options, $defaults );

		$start_time                    = time();
		$this->protected_skipped_count = 0;
		$this->protected_paths_cache   = null;

		$results = array(
			'thumbnails'  => array(),
			'webp'        => array(),
			'orphaned'    => array(),
			'error_files' => array(), // All error files from all scan types
			'statistics'  => array(
				'total_thumbnails'   => 0,
				'total_webp'         => 0,
				'total_orphaned'     => 0,
				'total_size'         => 0,
				'thumbnails_size'    => 0,
				'webp_size'          => 0,
				'orphaned_size'      => 0,
				'scan_time'          => 0,
				'total_scanned'      => 0,
				'total_errors'       => 0,
				'protected_skipped'  => 0,
				'protected_ids_count'=> count( $this->get_protected_attachment_ids() ),
			),
		);

		// Validate uploads directory before scanning
		if ( ! is_dir( $this->uploads_dir ) ) {
			$results['error_files'][] = array(
				'file'  => $this->uploads_dir,
				'error' => __( 'ไม่พบโฟลเดอร์ uploads', 'tonkla-image-optimizer' ),
				'type'  => 'system',
			);
			$results['statistics']['total_errors'] = count( $results['error_files'] );
			return $results;
		}
		
		if ( ! is_readable( $this->uploads_dir ) ) {
			$results['error_files'][] = array(
				'file'  => $this->uploads_dir,
				'error' => __( 'ไม่สามารถอ่านโฟลเดอร์ uploads ได้', 'tonkla-image-optimizer' ),
				'type'  => 'system',
			);
			$results['statistics']['total_errors'] = count( $results['error_files'] );
			return $results;
		}
		
		// Scan thumbnails
		if ( $options['scan_type'] === 'all' || $options['scan_type'] === 'thumbnails' ) {
			$thumbnails_result = $this->scan_unused_thumbnails( $options );
			
			// Handle result structure (new format with files/errors or old format)
			if ( isset( $thumbnails_result['files'] ) ) {
				$results['thumbnails'] = $thumbnails_result['files'];
				if ( ! empty( $thumbnails_result['errors'] ) ) {
					foreach ( $thumbnails_result['errors'] as $error ) {
						$error['type'] = 'thumbnails';
						$results['error_files'][] = $error;
					}
				}
				if ( isset( $thumbnails_result['statistics']['total_scanned'] ) ) {
					$results['statistics']['total_scanned'] += $thumbnails_result['statistics']['total_scanned'];
				}
			} else {
				// Old format - backward compatibility
				$results['thumbnails'] = $thumbnails_result;
			}

			$results['thumbnails']                     = $this->filter_protected_from_results( $results['thumbnails'] );
			$results['statistics']['total_thumbnails'] = count( $results['thumbnails'] );
			$results['statistics']['thumbnails_size']  = 0;
			foreach ( $results['thumbnails'] as $thumb ) {
				if ( isset( $thumb['size'] ) ) {
					$results['statistics']['thumbnails_size'] += $thumb['size'];
					$results['statistics']['total_size']      += $thumb['size'];
				}
			}

			// Check time limit
			if ( $options['time_limit'] > 0 && ( time() - $start_time ) > $options['time_limit'] ) {
				$results['statistics']['timeout'] = true;
				$results['statistics']['total_errors'] = count( $results['error_files'] );
				$results['statistics']['scan_time'] = time() - $start_time;
				return $results;
			}
		}

		// Scan WebP
		if ( $options['scan_type'] === 'all' || $options['scan_type'] === 'webp' ) {
			$webp_result = $this->scan_unused_webp( $options );
			
			// Handle result structure
			if ( isset( $webp_result['files'] ) ) {
				$results['webp'] = $webp_result['files'];
				if ( ! empty( $webp_result['errors'] ) ) {
					foreach ( $webp_result['errors'] as $error ) {
						$error['type'] = 'webp';
						$results['error_files'][] = $error;
					}
				}
				if ( isset( $webp_result['statistics']['total_scanned'] ) ) {
					$results['statistics']['total_scanned'] += $webp_result['statistics']['total_scanned'];
				}
			} else {
				// Old format
				$results['webp'] = $webp_result;
			}

			$results['webp']                     = $this->filter_protected_from_results( $results['webp'] );
			$results['statistics']['total_webp'] = count( $results['webp'] );
			$results['statistics']['webp_size']  = 0;
			foreach ( $results['webp'] as $webp ) {
				if ( isset( $webp['size'] ) ) {
					$results['statistics']['webp_size']  += $webp['size'];
					$results['statistics']['total_size'] += $webp['size'];
				}
			}

			// Check time limit
			if ( $options['time_limit'] > 0 && ( time() - $start_time ) > $options['time_limit'] ) {
				$results['statistics']['timeout'] = true;
				$results['statistics']['total_errors'] = count( $results['error_files'] );
				$results['statistics']['scan_time'] = time() - $start_time;
				return $results;
			}
		}

		// Scan orphaned images
		if ( $options['scan_type'] === 'all' || $options['scan_type'] === 'orphaned' ) {
			$orphaned_result = $this->scan_orphaned_images( $options );
			
			// Handle result structure
			if ( isset( $orphaned_result['files'] ) ) {
				$results['orphaned'] = $orphaned_result['files'];
				if ( ! empty( $orphaned_result['errors'] ) ) {
					foreach ( $orphaned_result['errors'] as $error ) {
						$error['type'] = 'orphaned';
						$results['error_files'][] = $error;
					}
				}
				if ( isset( $orphaned_result['statistics']['total_scanned'] ) ) {
					$results['statistics']['total_scanned'] += $orphaned_result['statistics']['total_scanned'];
				}
			} else {
				// Old format
				$results['orphaned'] = $orphaned_result;
			}

			$results['orphaned']                     = $this->filter_protected_from_results( $results['orphaned'] );
			$results['statistics']['total_orphaned'] = count( $results['orphaned'] );
			$results['statistics']['orphaned_size']  = 0;
			foreach ( $results['orphaned'] as $orphaned ) {
				if ( isset( $orphaned['size'] ) ) {
					$results['statistics']['orphaned_size'] += $orphaned['size'];
					$results['statistics']['total_size']    += $orphaned['size'];
				}
			}
		}

		$results['statistics']['protected_skipped'] = $this->protected_skipped_count;

		// Convert sizes to MB (ensure values exist before division)
		$results['statistics']['total_size_mb']       = round( ( isset( $results['statistics']['total_size'] ) ? $results['statistics']['total_size'] : 0 ) / 1024 / 1024, 2 );
		$results['statistics']['thumbnails_size_mb']  = round( ( isset( $results['statistics']['thumbnails_size'] ) ? $results['statistics']['thumbnails_size'] : 0 ) / 1024 / 1024, 2 );
		$results['statistics']['webp_size_mb']       = round( ( isset( $results['statistics']['webp_size'] ) ? $results['statistics']['webp_size'] : 0 ) / 1024 / 1024, 2 );
		$results['statistics']['orphaned_size_mb']   = round( ( isset( $results['statistics']['orphaned_size'] ) ? $results['statistics']['orphaned_size'] : 0 ) / 1024 / 1024, 2 );

		// Calculate scan time
		$results['statistics']['scan_time'] = time() - $start_time;
		$results['statistics']['total_errors'] = count( $results['error_files'] );

		return $results;
	}

	/**
	 * Validate file path (security check)
	 *
	 * @param string $file_path File path to validate.
	 * @return array Validation result with 'valid' and 'error' keys.
	 */
	public function validate_file_path( $file_path ) {
		$result = array(
			'valid' => false,
			'error' => '',
		);

		// Check if path is empty
		if ( empty( $file_path ) ) {
			$result['error'] = 'Empty file path';
			return $result;
		}

		// Normalize path
		$normalized_path = wp_normalize_path( $file_path );
		
		// Try to get real path
		$real_path = realpath( $normalized_path );
		
		// If realpath fails, try the normalized path directly
		if ( ! $real_path ) {
			// Check if normalized path exists
			if ( file_exists( $normalized_path ) ) {
				$real_path = $normalized_path;
			} else {
				// Log for debugging
				$this->log_action(
					'VALIDATE_PATH_FAILED',
					array(
						'original_path' => $file_path,
						'normalized_path' => $normalized_path,
						'exists' => file_exists( $normalized_path ),
					)
				);
				$result['error'] = 'Invalid file path or file does not exist';
				return $result;
			}
		}

		$real_path    = wp_normalize_path( $real_path );
		$uploads_dir  = wp_normalize_path( realpath( $this->uploads_dir ) );

		if ( ! $uploads_dir ) {
			$result['error'] = 'Uploads directory not found';
			return $result;
		}

		// Normalize both paths for comparison (handle Windows path case sensitivity)
		$real_path_lower = strtolower( $real_path );
		$uploads_dir_lower = strtolower( $uploads_dir );

		// Check if file is within uploads directory
		if ( strpos( $real_path_lower, $uploads_dir_lower ) !== 0 ) {
			$this->log_action(
				'VALIDATE_PATH_OUTSIDE',
				array(
					'file_path' => $real_path,
					'uploads_dir' => $uploads_dir,
				)
			);
			$result['error'] = 'File is outside uploads directory';
			return $result;
		}

		// Check if file exists
		if ( ! file_exists( $real_path ) ) {
			$result['error'] = 'File does not exist';
			return $result;
		}

		// Check if it's a file (not directory)
		if ( ! is_file( $real_path ) ) {
			$result['error'] = 'Path is not a file';
			return $result;
		}

		// Check if file is protected (whitelist)
		$filename = basename( $real_path );
		if ( in_array( $filename, $this->protected_files, true ) ) {
			$result['error'] = 'File is protected (whitelist)';
			return $result;
		}

		// Check if file is in protected directory
		$relative_path = str_replace( $uploads_dir . '/', '', $real_path );
		$path_parts    = explode( '/', $relative_path );
		if ( ! empty( $path_parts ) && in_array( $path_parts[0], $this->protected_dirs, true ) ) {
			$result['error'] = 'File is in protected directory';
			return $result;
		}

		// Never delete site branding assets (logo, favicon, Carbon Fields images)
		if ( $this->is_path_protected( $real_path ) ) {
			$result['error'] = 'File is a protected site asset (logo, favicon, or theme image)';
			return $result;
		}

		// Check file extension (only allow image files)
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $this->image_extensions, true ) ) {
			$result['error'] = 'File is not an image';
			return $result;
		}

		// All checks passed
		$result['valid'] = true;
		return $result;
	}

	/**
	 * Delete unused images
	 *
	 * @param array $file_paths Array of file paths to delete.
	 * @param array $options    Delete options (dry_run, batch_size, etc.).
	 * @return array Results with deleted, failed, and errors.
	 */
	public function delete_unused_images( $file_paths, $options = array() ) {
		$defaults = array(
			'dry_run'    => false,
			'batch_size' => 50,
			'log'        => true,
		);
		$options = wp_parse_args( $options, $defaults );

		$results = array(
			'deleted' => 0,
			'failed'  => 0,
			'errors'  => array(),
			'skipped' => 0,
		);

		// Log deletion start (only log summary, not every file)
		if ( $options['log'] && $options['dry_run'] ) {
			$this->log_action(
				'DELETE_START',
				array(
					'total_files' => count( $file_paths ),
					'dry_run'     => $options['dry_run'],
				)
			);
		}

		$processed = 0;
		foreach ( $file_paths as $file_path ) {
			// Skip empty paths
			if ( empty( $file_path ) ) {
				continue;
			}

			// Full validation (uploads boundary, whitelist, protected site assets)
			$validation = $this->validate_file_path( $file_path );
			if ( ! $validation['valid'] ) {
				$error_msg = isset( $validation['error'] ) ? $validation['error'] : 'Validation failed';
				// Already gone — count as deleted success
				if ( 'File does not exist' === $error_msg || 'Invalid file path or file does not exist' === $error_msg ) {
					$results['deleted']++;
					$processed++;
					continue;
				}
				// Protected / invalid — skip, do not delete
				$results['skipped']++;
				$results['errors'][] = array(
					'file'  => $file_path,
					'error' => $error_msg,
				);
				if ( $options['log'] ) {
					$this->log_action(
						'DELETE_SKIPPED',
						array(
							'file'  => $file_path,
							'error' => $error_msg,
						)
					);
				}
				$processed++;
				continue;
			}

			$real_path = $this->normalize_path( $file_path );
			if ( ! $real_path ) {
				$real_path = wp_normalize_path( $file_path );
			}

			// Dry run mode - don't actually delete
			if ( $options['dry_run'] ) {
				$results['deleted']++;
				if ( $options['log'] ) {
					$this->log_action(
						'DELETE_DRY_RUN',
						array(
							'file' => $real_path,
						)
					);
				}
				$processed++;
				continue;
			}

			// Try to delete
			$deleted      = false;
			$delete_error = '';

			// Check if file exists first
			if ( ! file_exists( $real_path ) ) {
				// File already deleted, count as success
				$deleted = true;
				if ( $options['log'] ) {
					$this->log_action(
						'DELETE_ALREADY_GONE',
						array(
							'file' => $real_path,
						)
					);
				}
			} else {
				// Attempt deletion directly (is_writable check can be unreliable on Windows)
				$deleted = @unlink( $real_path );
				if ( ! $deleted ) {
					// Check if file still exists to determine error
					if ( file_exists( $real_path ) ) {
						// File still exists, deletion failed
						$delete_error = 'Failed to delete file (permission denied or file locked)';
						if ( $options['log'] ) {
							$this->log_action(
								'DELETE_FAILED_DETAIL',
								array(
									'file' => $real_path,
									'is_readable' => is_readable( $real_path ),
									'is_writable' => is_writable( $real_path ),
									'perms' => fileperms( $real_path ),
								)
							);
						}
					} else {
						// File was deleted successfully (race condition)
						$deleted = true;
					}
				}
			}

			if ( $deleted ) {
				$results['deleted']++;
				// Only log failures, not every success (to reduce I/O overhead)
			} else {
				$results['failed']++;
				$results['errors'][] = array(
					'file'  => $real_path,
					'error' => $delete_error,
				);
				// Only log failures for debugging
				if ( $options['log'] ) {
					$this->log_action(
						'DELETE_FAILED',
						array(
							'file'  => $real_path,
							'error' => $delete_error,
						)
					);
				}
			}

			$processed++;
		}

		// Log deletion end
		if ( $options['log'] ) {
			$this->log_action(
				'DELETE_END',
				array(
					'deleted' => $results['deleted'],
					'failed'  => $results['failed'],
					'skipped' => $results['skipped'],
				)
			);
		}

		return $results;
	}

	/**
	 * Get deletion statistics
	 *
	 * @return array Statistics about deletions.
	 */
	public function get_deletion_stats() {
		// This could read from a log file or database
		// For now, return empty stats
		return array(
			'total_deleted' => 0,
			'total_failed'   => 0,
			'last_deletion' => null,
		);
	}

	/**
	 * Convert memory limit string to bytes
	 *
	 * @param string $memory_limit Memory limit string (e.g., '128M', '256M').
	 * @return int Memory limit in bytes.
	 */
	private function convert_to_bytes( $memory_limit ) {
		$memory_limit = trim( $memory_limit );
		if ( empty( $memory_limit ) ) {
			return 0;
		}
		
		$last = strtolower( $memory_limit[ strlen( $memory_limit ) - 1 ] );
		$value = (int) $memory_limit;
		
		switch ( $last ) {
			case 'g':
				$value *= 1024;
				// Fall through
			case 'm':
				$value *= 1024;
				// Fall through
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}

	/**
	 * Format bytes to human-readable string
	 *
	 * @param int $bytes Bytes to format.
	 * @return string Formatted string (e.g., '128 MB').
	 */
	private function format_bytes( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= pow( 1024, $pow );
		
		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}

