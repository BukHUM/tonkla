<?php
/**
 * Tonkla Suite helpers for new theme code (Phase 2, incremental).
 *
 * Legacy tonkla_* functions remain untouched; new code should use this namespace.
 *
 * @package Tonkla\Theme
 */

namespace Tonkla\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Suite-wide helpers.
 */
final class Suite {

	/**
	 * Build a tonkla.co credit URL with Suite UTM parameters.
	 */
	public static function credit_url( string $campaign = 'theme', string $content = 'footer' ): string {
		return add_query_arg(
			array(
				'utm_source'   => 'tonkla-suite',
				'utm_medium'   => 'credit',
				'utm_campaign' => $campaign,
				'utm_content'  => $content,
			),
			'https://tonkla.co'
		);
	}
}
