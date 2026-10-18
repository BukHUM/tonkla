<?php
/**
 * Icon rendering helper for page builder modules.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Allowed SVG tags/attrs for inline icon output.
 *
 * @return array<string,array<int,string>>
 */
function tonkla_svg_allowed_html() {
	$attrs = array(
		'xmlns',
		'viewBox',
		'viewbox',
		'width',
		'height',
		'fill',
		'stroke',
		'stroke-width',
		'stroke-linecap',
		'stroke-linejoin',
		'class',
		'aria-hidden',
		'role',
		'focusable',
		'd',
		'x',
		'y',
		'x1',
		'y1',
		'x2',
		'y2',
		'cx',
		'cy',
		'r',
		'rx',
		'ry',
		'points',
		'transform',
		'opacity',
	);

	$tags = array( 'svg', 'path', 'g', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse', 'title', 'desc' );
	$out  = array();
	foreach ( $tags as $tag ) {
		$out[ $tag ] = $attrs;
	}
	return $out;
}

/**
 * Resolve FA class string from item (legacy `icon` or `icon_class`).
 *
 * @param array<string,mixed> $item Module item.
 * @return string
 */
function tonkla_icon_class_from_item( array $item ) {
	if ( ! empty( $item['icon_class'] ) ) {
		return sanitize_text_field( (string) $item['icon_class'] );
	}
	if ( ! empty( $item['icon'] ) ) {
		return sanitize_text_field( (string) $item['icon'] );
	}
	return '';
}

/**
 * Resolve icon type (fa|image) with back-compat default.
 *
 * @param array<string,mixed> $item Module item.
 * @return string
 */
function tonkla_icon_type_from_item( array $item ) {
	$type = isset( $item['icon_type'] ) ? sanitize_key( (string) $item['icon_type'] ) : '';
	if ( in_array( $type, array( 'fa', 'image', 'icon' ), true ) ) {
		return ( 'icon' === $type ) ? 'fa' : $type;
	}
	if ( ! empty( $item['icon_image'] ) ) {
		return 'image';
	}
	return 'fa';
}

/**
 * Map icon color slug to Tailwind utility classes for text/bg tiles.
 *
 * @param string $color Color slug or legacy class string.
 * @param string $mode  'text' | 'tile' (bg + text).
 * @return string
 */
function tonkla_icon_color_classes( $color, $mode = 'text' ) {
	$color = trim( (string) $color );
	if ( '' === $color ) {
		$color = 'brand';
	}

	// Legacy full class strings.
	if ( str_starts_with( $color, 'text-' ) || str_starts_with( $color, 'bg-' ) ) {
		return $color;
	}

	// Legacy "red-500" form.
	if ( preg_match( '/^[a-z]+-\d{2,3}$/', $color ) ) {
		return 'text' === $mode ? 'text-' . $color : 'bg-' . preg_replace( '/-\d+$/', '-100', $color ) . ' text-' . $color;
	}

	$slug = sanitize_key( $color );
	if ( 'brand' === $slug ) {
		return 'text' === $mode ? 'text-brand' : 'bg-brand/10 text-brand';
	}

	$map = array(
		'slate'    => array( 'text-slate-500', 'bg-slate-100 text-slate-600' ),
		'red'      => array( 'text-red-500', 'bg-red-100 text-red-600' ),
		'orange'   => array( 'text-orange-500', 'bg-orange-100 text-orange-600' ),
		'amber'    => array( 'text-amber-500', 'bg-amber-100 text-amber-600' ),
		'yellow'   => array( 'text-yellow-500', 'bg-yellow-100 text-yellow-600' ),
		'lime'     => array( 'text-lime-500', 'bg-lime-100 text-lime-600' ),
		'green'    => array( 'text-green-500', 'bg-green-100 text-green-600' ),
		'emerald'  => array( 'text-emerald-500', 'bg-emerald-100 text-emerald-600' ),
		'teal'     => array( 'text-teal-500', 'bg-teal-100 text-teal-600' ),
		'cyan'     => array( 'text-cyan-500', 'bg-cyan-100 text-cyan-600' ),
		'sky'      => array( 'text-sky-500', 'bg-sky-100 text-sky-600' ),
		'blue'     => array( 'text-blue-500', 'bg-blue-100 text-blue-600' ),
		'indigo'   => array( 'text-indigo-500', 'bg-indigo-100 text-indigo-600' ),
		'violet'   => array( 'text-violet-500', 'bg-violet-100 text-violet-600' ),
		'purple'   => array( 'text-purple-500', 'bg-purple-100 text-purple-600' ),
		'fuchsia'  => array( 'text-fuchsia-500', 'bg-fuchsia-100 text-fuchsia-600' ),
		'pink'     => array( 'text-pink-500', 'bg-pink-100 text-pink-600' ),
		'rose'     => array( 'text-rose-500', 'bg-rose-100 text-rose-600' ),
	);

	if ( ! isset( $map[ $slug ] ) ) {
		return 'text' === $mode ? 'text-brand' : 'bg-brand/10 text-brand';
	}

	return 'text' === $mode ? $map[ $slug ][0] : $map[ $slug ][1];
}

/**
 * Hex map for admin color swatches (shared with JS).
 *
 * @return array<string,string>
 */
function tonkla_icon_color_hex_map() {
	$brand = function_exists( 'tonkla_brand_hex' ) ? tonkla_brand_hex() : '#10b981';
	return array(
		'brand'   => $brand,
		'slate'   => '#64748b',
		'red'     => '#ef4444',
		'orange'  => '#f97316',
		'amber'   => '#f59e0b',
		'yellow'  => '#eab308',
		'lime'    => '#84cc16',
		'green'   => '#22c55e',
		'emerald' => $brand,
		'teal'    => '#14b8a6',
		'cyan'    => '#06b6d4',
		'sky'     => '#0ea5e9',
		'blue'    => '#3b82f6',
		'indigo'  => '#6366f1',
		'violet'  => '#8b5cf6',
		'purple'  => '#a855f7',
		'fuchsia' => '#d946ef',
		'pink'    => '#ec4899',
		'rose'    => '#f43f5e',
		'white'   => '#ffffff',
		'light'   => '#f8fafc',
		'light-gray' => '#f1f5f9',
		'gray'    => '#e2e8f0',
		'dark-gray' => '#1e293b',
		'dark'    => '#0f172a',
	);
}

/**
 * Render an icon from a module item (FA class or attachment).
 *
 * @param array<string,mixed> $item Item data.
 * @param array<string,mixed> $args {
 *     @type string $class        Extra CSS classes on the icon element.
 *     @type string $color_mode   'text'|'tile'|''.
 *     @type string $fallback     Fallback FA class.
 *     @type bool   $echo         Echo or return.
 *     @type int    $image_size   Attachment size name or array — uses 'thumbnail' default.
 * }
 * @return string
 */
function tonkla_render_icon( array $item, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'      => '',
			'color_mode' => '',
			'fallback'   => 'fa-solid fa-circle',
			'echo'       => true,
			'image_size' => 'thumbnail',
		)
	);

	$type    = tonkla_icon_type_from_item( $item );
	$classes = trim( (string) $args['class'] );
	$html    = '';

	if ( 'image' === $type && ! empty( $item['icon_image'] ) ) {
		$attachment_id = absint( $item['icon_image'] );
		if ( $attachment_id ) {
			$mime = get_post_mime_type( $attachment_id );
			if ( 'image/svg+xml' === $mime ) {
				$path = get_attached_file( $attachment_id );
				if ( $path && is_readable( $path ) ) {
					$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local attachment path.
					if ( false !== $raw && '' !== $raw ) {
						$clean = tonkla_sanitize_svg_string( $raw );
						if ( '' !== $clean ) {
							if ( '' !== $classes && preg_match( '/<svg\b/i', $clean ) ) {
								$clean = preg_replace(
									'/<svg\b/i',
									'<svg class="' . esc_attr( $classes ) . '"',
									$clean,
									1
								);
							}
							$html = wp_kses( $clean, tonkla_svg_allowed_html() );
						}
					}
				}
			}
			if ( '' === $html ) {
				$html = wp_get_attachment_image(
					$attachment_id,
					$args['image_size'],
					false,
					array(
						'class' => $classes,
						'alt'   => '',
					)
				);
			}
		}
	}

	if ( '' === $html ) {
		$fa = tonkla_icon_class_from_item( $item );
		if ( '' === $fa ) {
			$fa = (string) $args['fallback'];
		}
		if ( '' !== $fa ) {
			$color_class = '';
			if ( '' !== $args['color_mode'] ) {
				$color_key   = isset( $item['icon_color'] ) ? (string) $item['icon_color'] : ( isset( $item['color_class'] ) ? (string) $item['color_class'] : 'brand' );
				$color_class = tonkla_icon_color_classes( $color_key, $args['color_mode'] );
			}
			$combined = trim( $fa . ' ' . $color_class . ' ' . $classes );
			$html     = '<i class="' . esc_attr( $combined ) . '" aria-hidden="true"></i>';
		}
	}

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above via kses / esc_attr / wp_get_attachment_image.
		return '';
	}

	return $html;
}

/**
 * Sanitize an SVG string using enshrined/svg-sanitize when available.
 *
 * @param string $svg Raw SVG markup.
 * @return string Clean SVG or empty string.
 */
function tonkla_sanitize_svg_string( $svg ) {
	$svg = (string) $svg;
	if ( '' === $svg ) {
		return '';
	}

	if ( class_exists( '\\enshrined\\svgSanitize\\Sanitizer' ) ) {
		$sanitizer = new \enshrined\svgSanitize\Sanitizer();
		$sanitizer->removeRemoteReferences( true );
		$clean = $sanitizer->sanitize( $svg );
		return is_string( $clean ) ? $clean : '';
	}

	// Fallback: strip scripts / event handlers coarsely.
	$svg = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $svg );
	$svg = preg_replace( '/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg );
	return is_string( $svg ) ? $svg : '';
}
