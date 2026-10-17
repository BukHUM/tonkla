<?php
/**
 * Front-end security headers (Best Practices / Trust & Safety basics).
 *
 * HSTS, full CSP, COOP, and Trusted Types belong at the server / CDN layer —
 * do not set them here (easy to break Site Kit, CF7, embeds).
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send conservative security headers on public HTML responses.
 *
 * @param array<string,string> $headers Response headers.
 * @return array<string,string>
 */
function tonkla_security_headers( $headers ) {
	if ( is_admin() ) {
		return $headers;
	}

	// Clickjacking: allow same-origin embeds only (admin/customizer stay usable).
	$headers['X-Frame-Options'] = 'SAMEORIGIN';

	// MIME sniffing.
	$headers['X-Content-Type-Options'] = 'nosniff';

	// Referrer leakage.
	$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';

	// Lock down powerful APIs not used by the theme.
	$headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=(), payment=(), interest-cohort=()';

	return $headers;
}
add_filter( 'wp_headers', 'tonkla_security_headers' );
