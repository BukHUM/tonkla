<?php
/**
 * Shared nav menu badge / icon helpers.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map badge color keys to Tailwind classes.
 *
 * @param string $badge_color Color key.
 * @return string
 */
function tonkla_menu_badge_classes( $badge_color ) {
	$map = array(
		'red'     => 'bg-red-100 text-red-600',
		'emerald' => 'bg-emerald-100 text-emerald-600',
		'brand'   => 'bg-brand/10 text-brand',
		'blue'    => 'bg-blue-100 text-blue-600',
	);

	if ( isset( $map[ $badge_color ] ) ) {
		return $map[ $badge_color ];
	}

	if ( $badge_color ) {
		$safe = sanitize_html_class( $badge_color );
		return 'bg-' . $safe . '-100 text-' . $safe . '-600';
	}

	return 'bg-gray-100 text-gray-800';
}

/**
 * Map icon color keys to Tailwind text classes.
 *
 * @param string $icon_color Color key or legacy class.
 * @return string
 */
function tonkla_menu_icon_text_class( $icon_color ) {
	if ( 'brand' === $icon_color ) {
		return 'text-brand';
	}

	if ( ! empty( $icon_color ) && 0 === strpos( $icon_color, 'text-' ) ) {
		return $icon_color;
	}

	$map = array(
		'indigo' => 'text-indigo-500',
		'orange' => 'text-orange-500',
		'blue'   => 'text-blue-500',
		'red'    => 'text-red-500',
		'purple' => 'text-purple-500',
		'pink'   => 'text-pink-500',
		'teal'   => 'text-teal-500',
		'yellow' => 'text-yellow-500',
		'cyan'   => 'text-cyan-500',
		'lime'   => 'text-lime-500',
		'slate'  => 'text-slate-400',
	);

	if ( isset( $map[ $icon_color ] ) ) {
		return $map[ $icon_color ];
	}

	if ( ! empty( $icon_color ) ) {
		return 'text-' . sanitize_html_class( $icon_color ) . '-500';
	}

	return 'text-slate-400';
}
