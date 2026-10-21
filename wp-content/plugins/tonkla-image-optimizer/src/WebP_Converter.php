<?php
namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_WebP_Converter singleton. */
final class WebP_Converter {
	public static function get_instance() {
		return class_exists( 'IO_WebP_Converter' ) ? \IO_WebP_Converter::get_instance() : null;
	}
	/** Dual-read WebP meta: new key first, legacy _webp_file fallback. */
	public static function get_webp_file( int $attachment_id ) {
		$new = get_post_meta( $attachment_id, '_tonkla_img_webp_file', true );
		if ( is_string( $new ) && '' !== $new ) {
			return $new;
		}
		return get_post_meta( $attachment_id, '_webp_file', true );
	}
}
