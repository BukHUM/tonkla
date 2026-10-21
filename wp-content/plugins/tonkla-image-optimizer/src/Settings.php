<?php
/**
 * Settings with dual-read: tonkla_img_* first, io_image_* fallback.
 *
 * @package Tonkla\ImageOptimizer
 */

namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;

/**
 * Dual-read settings bridge (Phase 2). Writes go to new keys; old keys kept.
 */
final class Settings {

	/** Map new suffix => legacy option name. */
	private static function map(): array {
		return array(
			'auto_resize'    => 'io_image_auto_resize',
			'max_width'      => 'io_image_max_width',
			'max_height'     => 'io_image_max_height',
			'maintain_aspect' => 'io_image_maintain_aspect',
			'jpeg_quality'   => 'io_image_jpeg_quality',
			'max_file_size'  => 'io_image_max_file_size',
			'webp_enabled'   => 'io_image_webp_enabled',
			'webp_quality'   => 'io_image_webp_quality',
			'strip_exif'     => 'io_image_strip_exif',
		);
	}

	public static function get( string $key, $default = false ) {
		$map = self::map();
		if ( ! isset( $map[ $key ] ) ) {
			return $default;
		}
		$new = get_option( 'tonkla_img_' . $key, false );
		if ( false !== $new ) {
			return $new;
		}
		return get_option( $map[ $key ], $default );
	}

	public static function update( string $key, $value ): bool {
		$map = self::map();
		if ( ! isset( $map[ $key ] ) ) {
			return false;
		}
		return (bool) update_option( 'tonkla_img_' . $key, $value );
	}

	/** Idempotent migration: copy legacy values to new keys when missing. */
	public static function migrate(): void {
		foreach ( self::map() as $suffix => $legacy ) {
			if ( false === get_option( 'tonkla_img_' . $suffix, false ) ) {
				$old = get_option( $legacy, false );
				if ( false !== $old ) {
					add_option( 'tonkla_img_' . $suffix, $old, '', false );
				}
			}
		}
		if ( false === get_option( 'tonkla_img_version', false ) ) {
			add_option( 'tonkla_img_version', defined( 'TONKLA_IMG_VERSION' ) ? TONKLA_IMG_VERSION : '1.0.10', '', false );
		}
	}
}
