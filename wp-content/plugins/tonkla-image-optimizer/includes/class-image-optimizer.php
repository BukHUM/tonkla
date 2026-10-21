<?php
/**
 * Image Optimizer Class
 * Handles image resizing and EXIF stripping
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimizer Class
 */
class IO_Image_Optimizer {

	/**
	 * Instance
	 *
	 * @var IO_Image_Optimizer
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return IO_Image_Optimizer
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Resize image on upload
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'resize_uploaded_image' ), 10, 1 );

		// Strip EXIF data
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'strip_exif_data' ), 20, 1 );
	}

	/**
	 * Resize image on upload
	 *
	 * @param array $file Uploaded file array.
	 * @return array Modified file array.
	 */
	public function resize_uploaded_image( $file ) {
		// Validate input
		if ( ! is_array( $file ) || ! isset( $file['tmp_name'] ) || ! isset( $file['type'] ) ) {
			return $file;
		}

		// Check if file exists
		if ( ! file_exists( $file['tmp_name'] ) ) {
			return $file;
		}

		// Check if auto resize is enabled
		$auto_resize = get_option( 'io_image_auto_resize', '1' );
		if ( $auto_resize !== '1' ) {
			return $file;
		}

		// Check if it's an image
		$image_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );
		if ( ! in_array( $file['type'], $image_types, true ) ) {
			return $file;
		}

		// Get settings
		$max_width = absint( get_option( 'io_image_max_width', 1920 ) );
		$max_height = absint( get_option( 'io_image_max_height', 1080 ) );
		$maintain_aspect = get_option( 'io_image_maintain_aspect', '1' ) === '1';
		$max_file_size = absint( get_option( 'io_image_max_file_size', 0 ) ); // 0 = resize all

		// Validate dimensions
		if ( $max_width < 100 || $max_width > 10000 ) {
			$max_width = 1920;
		}
		if ( $max_height < 100 || $max_height > 10000 ) {
			$max_height = 1080;
		}

		// Check file size threshold
		if ( $max_file_size > 0 && isset( $file['size'] ) && $file['size'] < ( $max_file_size * 1024 * 1024 ) ) {
			return $file; // Skip small images
		}

		// Load image
		$image = wp_get_image_editor( $file['tmp_name'] );
		if ( is_wp_error( $image ) ) {
			error_log( '[Image Optimization] Failed to load image: ' . $image->get_error_message() );
			return $file;
		}

		// Get original dimensions
		$size = $image->get_size();
		if ( ! $size || ! isset( $size['width'] ) || ! isset( $size['height'] ) ) {
			return $file;
		}

		$width = absint( $size['width'] );
		$height = absint( $size['height'] );

		// Validate dimensions
		if ( $width <= 0 || $height <= 0 ) {
			return $file;
		}

		// Check if resize is needed
		if ( $width <= $max_width && $height <= $max_height ) {
			return $file; // No resize needed
		}

		// Calculate new dimensions
		if ( $maintain_aspect ) {
			$ratio = min( $max_width / $width, $max_height / $height );
			$new_width = max( 1, round( $width * $ratio ) );
			$new_height = max( 1, round( $height * $ratio ) );
		} else {
			$new_width = max( 1, $max_width );
			$new_height = max( 1, $max_height );
		}

		// Resize image
		$resized = $image->resize( $new_width, $new_height, false );
		if ( is_wp_error( $resized ) ) {
			error_log( '[Image Optimization] Failed to resize image: ' . $resized->get_error_message() );
			return $file;
		}

		// Save resized image
		$saved = $image->save( $file['tmp_name'] );
		if ( is_wp_error( $saved ) ) {
			error_log( '[Image Optimization] Failed to save resized image: ' . $saved->get_error_message() );
			return $file;
		}

		// Update file size
		if ( file_exists( $file['tmp_name'] ) ) {
			$file['size'] = filesize( $file['tmp_name'] );
		}

		return $file;
	}

	/**
	 * Strip EXIF data from images
	 *
	 * @param array $file Uploaded file array.
	 * @return array Modified file array.
	 */
	public function strip_exif_data( $file ) {
		// Validate input
		if ( ! is_array( $file ) || ! isset( $file['tmp_name'] ) || ! isset( $file['type'] ) ) {
			return $file;
		}

		// Check if file exists
		if ( ! file_exists( $file['tmp_name'] ) ) {
			return $file;
		}

		// Check if strip EXIF is enabled
		$strip_exif = get_option( 'io_image_strip_exif', '1' );
		if ( $strip_exif !== '1' ) {
			return $file;
		}

		// Only for JPEG images
		if ( $file['type'] !== 'image/jpeg' && $file['type'] !== 'image/jpg' ) {
			return $file;
		}

		// Load image
		$image = wp_get_image_editor( $file['tmp_name'] );
		if ( is_wp_error( $image ) ) {
			return $file;
		}

		// Save without EXIF (this strips EXIF automatically)
		$saved = $image->save( $file['tmp_name'] );
		if ( is_wp_error( $saved ) ) {
			return $file;
		}

		// Update file size
		$file['size'] = filesize( $file['tmp_name'] );

		return $file;
	}

	/**
	 * Regenerate image sizes for a single attachment
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function regenerate_image( $attachment_id, $options = array() ) {
		$defaults = array(
			'regenerate_type' => 'all', // 'all', 'resize', 'webp'
			'regenerate_thumbnails' => false,
		);
		$options = wp_parse_args( $options, $defaults );
		
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return new WP_Error( 'no_file', __( 'File not found.', 'tonkla-image-optimizer' ) );
		}

		$regenerate_type = $options['regenerate_type'];
		$regenerate_thumbnails = $options['regenerate_thumbnails'];
		
		// Handle resize
		if ( $regenerate_type === 'all' || $regenerate_type === 'resize' ) {
			// Resize main image if auto resize is enabled
			$auto_resize = get_option( 'io_image_auto_resize', '1' );
			if ( $auto_resize === '1' ) {
				$max_width = absint( get_option( 'io_image_max_width', 1920 ) );
				$max_height = absint( get_option( 'io_image_max_height', 1080 ) );
				$maintain_aspect = get_option( 'io_image_maintain_aspect', '1' ) === '1';
				$jpeg_quality = absint( get_option( 'io_image_jpeg_quality', 85 ) );
				
				$image = wp_get_image_editor( $file );
				if ( ! is_wp_error( $image ) ) {
					$image->set_quality( $jpeg_quality );
					$image->resize( $max_width, $max_height, $maintain_aspect );
					$image->save( $file );
				}
			}
		}
		
		// Regenerate metadata and thumbnails
		if ( $regenerate_thumbnails || $regenerate_type === 'all' || $regenerate_type === 'resize' ) {
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
			if ( ! is_wp_error( $metadata ) ) {
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		} else {
			// Just get existing metadata for WebP conversion
			$metadata = wp_get_attachment_metadata( $attachment_id );
		}

		// Convert to WebP if enabled
		if ( ( $regenerate_type === 'all' || $regenerate_type === 'webp' ) && get_option( 'io_image_webp_enabled', '1' ) === '1' ) {
			if ( ! empty( $metadata ) ) {
				$webp_converter = IO_WebP_Converter::get_instance();
				$webp_converter->convert_to_webp( $metadata, $attachment_id );
			}
		}

		return true;
	}

	/**
	 * Get image optimization statistics
	 *
	 * @return array Statistics array.
	 */
	public function get_image_stats() {
		$stats = array(
			'total_images'      => 0,
			'total_size'        => 0,
			'optimized_images'  => 0,
			'webp_images'       => 0,
			'recent_optimized'  => array(),
			'settings_status'   => array(),
		);

		// Get settings status
		$stats['settings_status'] = array(
			'auto_resize'  => get_option( 'io_image_auto_resize', '1' ) === '1',
			'webp_enabled' => get_option( 'io_image_webp_enabled', '1' ) === '1',
			'strip_exif'   => get_option( 'io_image_strip_exif', '1' ) === '1',
			'max_width'    => get_option( 'io_image_max_width', 1920 ),
			'max_height'   => get_option( 'io_image_max_height', 1080 ),
			'jpeg_quality' => get_option( 'io_image_jpeg_quality', 85 ),
			'webp_quality' => get_option( 'io_image_webp_quality', 85 ),
		);

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'lang'           => '', // Polylang: all languages.
			)
		);

		$stats['total_images'] = count( $attachments );
		$recent_count          = 0;
		$max_recent            = 5;

		foreach ( $attachments as $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			if ( $file && file_exists( $file ) ) {
				$file_size           = filesize( $file );
				$stats['total_size'] += $file_size;

				// Get image metadata
				$metadata = wp_get_attachment_metadata( $attachment_id );
				$width    = isset( $metadata['width'] ) ? $metadata['width'] : 0;
				$height   = isset( $metadata['height'] ) ? $metadata['height'] : 0;

				// Check if image is optimized (resized)
				$max_width  = $stats['settings_status']['max_width'];
				$max_height = $stats['settings_status']['max_height'];
				$is_resized = ( $width > 0 && $height > 0 ) && ( $width <= $max_width && $height <= $max_height );

				if ( $is_resized ) {
					$stats['optimized_images']++;
				}

				// Check if has WebP version
				$webp_file = \Tonkla\ImageOptimizer\WebP_Converter::get_webp_file( (int) $attachment_id );
				$has_webp  = $webp_file && file_exists( $webp_file );

				if ( $has_webp ) {
					$stats['webp_images']++;
				}

				// Get recent optimized images (last 5)
				if ( $recent_count < $max_recent && ( $is_resized || $has_webp ) ) {
					$image_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
					$webp_url  = $has_webp ? io_get_webp_url( $image_url, $attachment_id ) : '';

					$stats['recent_optimized'][] = array(
						'id'         => $attachment_id,
						'title'      => get_the_title( $attachment_id ),
						'url'        => $image_url,
						'webp_url'   => $webp_url,
						'has_webp'   => $has_webp,
						'is_resized' => $is_resized,
						'width'      => $width,
						'height'     => $height,
						'size'       => round( $file_size / 1024, 2 ), // KB
						'date'       => get_the_date( 'Y-m-d H:i', $attachment_id ),
					);
					$recent_count++;
				}
			}
		}

		// Convert bytes to MB
		$stats['total_size_mb'] = round( $stats['total_size'] / 1024 / 1024, 2 );

		// Calculate optimization percentage
		if ( $stats['total_images'] > 0 ) {
			$stats['optimization_percentage'] = round( ( $stats['optimized_images'] / $stats['total_images'] ) * 100, 1 );
			$stats['webp_percentage']          = round( ( $stats['webp_images'] / $stats['total_images'] ) * 100, 1 );
		} else {
			$stats['optimization_percentage'] = 0;
			$stats['webp_percentage']          = 0;
		}

		return $stats;
	}
}
