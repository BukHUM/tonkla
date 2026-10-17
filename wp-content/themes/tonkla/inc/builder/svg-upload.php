<?php
/**
 * Allow sanitized SVG uploads for administrators.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether current user may upload SVG icons.
 *
 * @return bool
 */
function tonkla_user_can_upload_svg() {
	return current_user_can( 'manage_options' );
}

/**
 * Add SVG mime for allowed users.
 *
 * @param array<string,string> $mimes Mime map.
 * @return array<string,string>
 */
function tonkla_upload_mimes_svg( $mimes ) {
	if ( tonkla_user_can_upload_svg() ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'tonkla_upload_mimes_svg' );

/**
 * Fix filetype detection for SVG.
 *
 * @param array<string,mixed> $data     File data.
 * @param string              $file     Full path.
 * @param string              $filename Filename.
 * @param array<string,string>|null $mimes Mimes.
 * @return array<string,mixed>
 */
function tonkla_check_filetype_svg( $data, $file, $filename, $mimes ) {
	if ( ! tonkla_user_can_upload_svg() ) {
		return $data;
	}
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( 'svg' === $ext || 'svgz' === $ext ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'tonkla_check_filetype_svg', 10, 4 );

/**
 * Sanitize SVG on upload; reject if unclean or oversized.
 *
 * @param array<string,mixed> $file Upload file array.
 * @return array<string,mixed>
 */
function tonkla_handle_svg_upload( $file ) {
	if ( empty( $file['type'] ) || 'image/svg+xml' !== $file['type'] ) {
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( 'svg' !== $ext && 'svgz' !== $ext ) {
			return $file;
		}
	}

	if ( ! tonkla_user_can_upload_svg() ) {
		$file['error'] = __( 'คุณไม่มีสิทธิ์อัปโหลดไฟล์ SVG', 'tonkla' );
		return $file;
	}

	$max_bytes = 200 * 1024;
	if ( ! empty( $file['size'] ) && (int) $file['size'] > $max_bytes ) {
		$file['error'] = __( 'ไฟล์ SVG ใหญ่เกิน 200KB', 'tonkla' );
		return $file;
	}

	if ( empty( $file['tmp_name'] ) || ! is_readable( $file['tmp_name'] ) ) {
		$file['error'] = __( 'อ่านไฟล์ SVG ไม่ได้', 'tonkla' );
		return $file;
	}

	$raw = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $raw || '' === $raw ) {
		$file['error'] = __( 'ไฟล์ SVG ว่างเปล่า', 'tonkla' );
		return $file;
	}

	$clean = tonkla_sanitize_svg_string( $raw );
	if ( '' === $clean || ! preg_match( '/<svg\b/i', $clean ) ) {
		$file['error'] = __( 'ไฟล์ SVG ไม่ปลอดภัยหรือรูปแบบไม่ถูกต้อง', 'tonkla' );
		return $file;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$written = file_put_contents( $file['tmp_name'], $clean );
	if ( false === $written ) {
		$file['error'] = __( 'บันทึกไฟล์ SVG ที่ทำความสะอาดแล้วไม่สำเร็จ', 'tonkla' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'tonkla_handle_svg_upload' );
