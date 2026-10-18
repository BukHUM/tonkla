<?php
/**
 * Admin chrome: self-hosted fonts + B2B menu/bar colors while this theme is active.
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

	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	// Same self-hosted Prompt + Sarabun as the front end (no Google Fonts round-trip).
	wp_enqueue_style(
		'tonkla-admin-fonts',
		$theme_uri . '/assets/css/fonts.css',
		array(),
		(string) filemtime( $theme_dir . '/assets/css/fonts.css' )
	);

	// Load after core admin CSS so menu width + content margin stay in sync.
	wp_enqueue_style(
		'tonkla-admin-chrome',
		$theme_uri . '/assets/css/admin-chrome.css',
		array( 'tonkla-admin-fonts', 'common', 'forms' ),
		(string) filemtime( $theme_dir . '/assets/css/admin-chrome.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'tonkla_enqueue_admin_chrome', 100 );

/**
 * Mark admin body for chrome + Tonkla settings page selectors.
 *
 * @param string $classes Space-separated classes.
 * @return string
 */
function tonkla_admin_body_class( $classes ) {
	$classes = trim( $classes . ' tonkla-admin-chrome' );

	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' === $page ) {
		return $classes;
	}

	if ( defined( 'TONKLA_ADMIN_SLUG_PARENT' ) && TONKLA_ADMIN_SLUG_PARENT === $page ) {
		$classes .= ' tonkla-admin-page tonkla-admin-overview';
	} elseif ( defined( 'TONKLA_ADMIN_SLUG_SITE' ) && TONKLA_ADMIN_SLUG_SITE === $page ) {
		$classes .= ' tonkla-admin-page tonkla-admin-settings';
	} elseif ( defined( 'TONKLA_ADMIN_SLUG_MARKETING' ) && TONKLA_ADMIN_SLUG_MARKETING === $page ) {
		$classes .= ' tonkla-admin-page tonkla-admin-settings';
	} elseif ( defined( 'TONKLA_ADMIN_SLUG_TOOLS' ) && TONKLA_ADMIN_SLUG_TOOLS === $page ) {
		$classes .= ' tonkla-admin-page tonkla-admin-tools';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'tonkla_admin_body_class' );
