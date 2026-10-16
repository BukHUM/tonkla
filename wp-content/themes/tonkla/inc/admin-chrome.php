<?php
/**
 * Admin chrome: Google Sans Flex + B2B menu/bar colors while this theme is active.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin chrome fonts and stylesheet on every wp-admin screen.
 *
 * @param string $hook_suffix Current admin page (unused).
 */
function tonkla_enqueue_admin_chrome( $hook_suffix = '' ) {
	unset( $hook_suffix );

	wp_enqueue_style(
		'tonkla-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'tonkla-admin-chrome',
		get_template_directory_uri() . '/assets/css/admin-chrome.css',
		array( 'tonkla-admin-fonts' ),
		(string) filemtime( get_template_directory() . '/assets/css/admin-chrome.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'tonkla_enqueue_admin_chrome', 5 );

/**
 * Preconnect Google Fonts on admin for faster first paint.
 *
 * @param array  $urls          URLs to print.
 * @param string $relation_type Relation type.
 * @return array
 */
function tonkla_admin_resource_hints( $urls, $relation_type ) {
	if ( ! is_admin() || 'preconnect' !== $relation_type ) {
		return $urls;
	}

	$urls[] = array(
		'href'        => 'https://fonts.googleapis.com',
		'crossorigin' => 'anonymous',
	);
	$urls[] = array(
		'href'        => 'https://fonts.gstatic.com',
		'crossorigin' => 'anonymous',
	);

	return $urls;
}
add_filter( 'wp_resource_hints', 'tonkla_admin_resource_hints', 10, 2 );

/**
 * Mark admin body for chrome-specific selectors if needed later.
 *
 * @param string $classes Space-separated classes.
 * @return string
 */
function tonkla_admin_body_class( $classes ) {
	return trim( $classes . ' tonkla-admin-chrome' );
}
add_filter( 'admin_body_class', 'tonkla_admin_body_class' );
