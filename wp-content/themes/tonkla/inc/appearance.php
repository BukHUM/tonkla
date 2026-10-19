<?php
/**
 * Front-end appearance: color presets, typography roles, CSS custom properties.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Brand color presets (light / DEFAULT / dark as RGB triplets for Tailwind alpha).
 *
 * @return array<string,array{label:string,light:string,base:string,dark:string,hex:string}>
 */
function tonkla_color_presets(): array {
	return array(
		'emerald' => array(
			'label' => __( 'เขียวมรกต (ค่าเริ่มต้น)', 'tonkla' ),
			'light' => '52 211 153',
			'base'  => '16 185 129',
			'dark'  => '4 120 87',
			'hex'   => '#10b981',
		),
		'teal'    => array(
			'label' => __( 'เขียวน้ำทะเล', 'tonkla' ),
			'light' => '45 212 191',
			'base'  => '20 184 166',
			'dark'  => '15 118 110',
			'hex'   => '#14b8a6',
		),
		'sky'     => array(
			'label' => __( 'ฟ้าสด', 'tonkla' ),
			'light' => '56 189 248',
			'base'  => '14 165 233',
			'dark'  => '3 105 161',
			'hex'   => '#0ea5e9',
		),
		'indigo'  => array(
			'label' => __( 'คราม', 'tonkla' ),
			'light' => '129 140 248',
			'base'  => '99 102 241',
			'dark'  => '67 56 202',
			'hex'   => '#6366f1',
		),
		'violet'  => array(
			'label' => __( 'ม่วง', 'tonkla' ),
			'light' => '167 139 250',
			'base'  => '139 92 246',
			'dark'  => '109 40 217',
			'hex'   => '#8b5cf6',
		),
		'rose'    => array(
			'label' => __( 'ชมพูโรส', 'tonkla' ),
			'light' => '251 113 133',
			'base'  => '244 63 94',
			'dark'  => '190 18 60',
			'hex'   => '#f43f5e',
		),
		'amber'   => array(
			'label' => __( 'อำพัน', 'tonkla' ),
			'light' => '251 191 36',
			'base'  => '245 158 11',
			'dark'  => '180 83 9',
			'hex'   => '#f59e0b',
		),
		'slate'   => array(
			'label' => __( 'เทาเข้ม (ธุรกิจ)', 'tonkla' ),
			'light' => '148 163 184',
			'base'  => '71 85 105',
			'dark'  => '30 41 59',
			'hex'   => '#475569',
		),
		'custom'  => array(
			'label' => __( 'กำหนดเอง', 'tonkla' ),
			'light' => '52 211 153',
			'base'  => '16 185 129',
			'dark'  => '4 120 87',
			'hex'   => '#10b981',
		),
	);
}

/**
 * Font choices: self-hosted defaults + curated Google Fonts (Thai-capable).
 *
 * @return array<string,array{label:string,family:string,source:string,weights:array<int>,google?:string}>
 */
function tonkla_font_catalog(): array {
	return array(
		'sarabun'            => array(
			'label'   => __( 'Sarabun (ค่าเริ่มต้น — โฮสต์ในธีม)', 'tonkla' ),
			'family'  => '"Sarabun", sans-serif',
			'source'  => 'local',
			'weights' => array( 400, 600, 700 ),
		),
		'prompt'             => array(
			'label'   => __( 'Prompt (ค่าเริ่มต้นหัวข้อ — โฮสต์ในธีม)', 'tonkla' ),
			'family'  => '"Prompt", sans-serif',
			'source'  => 'local',
			'weights' => array( 400, 600, 700 ),
		),
		'noto_sans_thai'     => array(
			'label'   => 'Noto Sans Thai',
			'family'  => '"Noto Sans Thai", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Noto+Sans+Thai:wght@400;600;700',
		),
		'ibm_plex_sans_thai' => array(
			'label'   => 'IBM Plex Sans Thai',
			'family'  => '"IBM Plex Sans Thai", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'IBM+Plex+Sans+Thai:wght@400;600;700',
		),
		'kanit'              => array(
			'label'   => 'Kanit',
			'family'  => '"Kanit", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Kanit:wght@400;600;700',
		),
		'chakra_petch'       => array(
			'label'   => 'Chakra Petch',
			'family'  => '"Chakra Petch", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Chakra+Petch:wght@400;600;700',
		),
		'anuphan'            => array(
			'label'   => 'Anuphan',
			'family'  => '"Anuphan", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Anuphan:wght@400;600;700',
		),
		'inter'              => array(
			'label'   => __( 'Inter (เน้นละติน — ไทยใช้ฟอนต์สำรอง)', 'tonkla' ),
			'family'  => '"Inter", "Sarabun", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Inter:wght@400;600;700',
		),
		'google_sans'        => array(
			'label'   => __( 'Google Sans (เน้นละติน — ไทยใช้ฟอนต์สำรอง)', 'tonkla' ),
			'family'  => '"Google Sans", "Sarabun", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Google+Sans:wght@400;600;700',
		),
		'system'             => array(
			'label'   => __( 'ฟอนต์ระบบ (เร็วที่สุด)', 'tonkla' ),
			'family'  => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
			'source'  => 'system',
			'weights' => array(),
		),
	);
}

/**
 * Typography role slugs.
 *
 * @return array<int,string>
 */
function tonkla_typography_roles(): array {
	return array( 'body', 'heading', 'nav', 'caption' );
}

/**
 * Default role config (matches legacy theme look).
 *
 * @return array<string,array{font:string,size:string,weight:string,color:string}>
 */
function tonkla_typography_role_defaults(): array {
	return array(
		'body'    => array(
			'font'   => 'sarabun',
			'size'   => '16',
			'weight' => '400',
			'color'  => '',
		),
		'heading' => array(
			'font'   => 'prompt',
			'size'   => 'scale',
			'weight' => '600',
			'color'  => '',
		),
		'nav'     => array(
			'font'   => 'prompt',
			'size'   => '14',
			'weight' => '500',
			'color'  => '',
		),
		'caption' => array(
			'font'   => 'sarabun',
			'size'   => '12',
			'weight' => '400',
			'color'  => '',
		),
	);
}

/**
 * Size options for body / nav / caption roles.
 *
 * @return array<string,string>
 */
function tonkla_font_size_options(): array {
	return array(
		'12' => '12px',
		'13' => '13px',
		'14' => '14px',
		'15' => '15px',
		'16' => '16px (' . __( 'ค่าเริ่มต้นเนื้อหา', 'tonkla' ) . ')',
		'17' => '17px',
		'18' => '18px',
	);
}

/**
 * Heading size scale presets (H1/H2/H3 relative to base).
 *
 * @return array<string,string>
 */
function tonkla_heading_scale_options(): array {
	return array(
		'compact' => __( 'กะทัดรัด', 'tonkla' ),
		'scale'   => __( 'มาตรฐาน (ค่าเริ่มต้น)', 'tonkla' ),
		'large'   => __( 'ใหญ่', 'tonkla' ),
	);
}

/**
 * Font weight options.
 *
 * @return array<string,string>
 */
function tonkla_font_weight_options(): array {
	return array(
		'400' => 'Regular (400)',
		'500' => 'Medium (500)',
		'600' => 'Semi Bold (600)',
		'700' => 'Bold (700)',
	);
}

/**
 * Select options for Carbon Fields color preset.
 *
 * @return array<string,string>
 */
function tonkla_color_preset_options(): array {
	$out = array();
	foreach ( tonkla_color_presets() as $slug => $preset ) {
		$out[ $slug ] = $preset['label'];
	}
	return $out;
}

/**
 * Select options for Carbon Fields font fields.
 *
 * @return array<string,string>
 */
function tonkla_font_options(): array {
	$out = array();
	foreach ( tonkla_font_catalog() as $slug => $font ) {
		$out[ $slug ] = $font['label'];
	}
	return $out;
}

/**
 * Sanitize hex color to #rrggbb or empty string.
 *
 * @param string $hex Raw hex.
 * @return string
 */
function tonkla_sanitize_hex_color( string $hex ): string {
	$hex = trim( $hex );
	if ( '' === $hex ) {
		return '';
	}
	if ( '#' !== $hex[0] ) {
		$hex = '#' . $hex;
	}
	if ( preg_match( '/^#([A-Fa-f0-9]{3})$/', $hex, $m ) ) {
		$h = $m[1];
		return strtolower( '#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2] );
	}
	if ( preg_match( '/^#([A-Fa-f0-9]{6})$/', $hex ) ) {
		return strtolower( $hex );
	}
	return '';
}

/**
 * Convert #rrggbb to "R G B" for CSS rgb().
 *
 * @param string $hex Hex color.
 * @return string
 */
function tonkla_hex_to_rgb_triplet( string $hex ): string {
	$hex = tonkla_sanitize_hex_color( $hex );
	if ( '' === $hex ) {
		return '16 185 129';
	}
	$r = hexdec( substr( $hex, 1, 2 ) );
	$g = hexdec( substr( $hex, 3, 2 ) );
	$b = hexdec( substr( $hex, 5, 2 ) );
	return $r . ' ' . $g . ' ' . $b;
}

/**
 * Darken a hex color by a factor (0–1 amount toward black).
 *
 * @param string $hex    Hex.
 * @param float  $amount 0.2 = 20% darker.
 * @return string #rrggbb
 */
function tonkla_hex_darken( string $hex, float $amount = 0.25 ): string {
	$hex = tonkla_sanitize_hex_color( $hex );
	if ( '' === $hex ) {
		return '#047857';
	}
	$amount = max( 0.0, min( 1.0, $amount ) );
	$r      = (int) round( hexdec( substr( $hex, 1, 2 ) ) * ( 1 - $amount ) );
	$g      = (int) round( hexdec( substr( $hex, 3, 2 ) ) * ( 1 - $amount ) );
	$b      = (int) round( hexdec( substr( $hex, 5, 2 ) ) * ( 1 - $amount ) );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Lighten a hex color.
 *
 * @param string $hex    Hex.
 * @param float  $amount 0.25 = blend 25% toward white.
 * @return string #rrggbb
 */
function tonkla_hex_lighten( string $hex, float $amount = 0.25 ): string {
	$hex = tonkla_sanitize_hex_color( $hex );
	if ( '' === $hex ) {
		return '#34d399';
	}
	$amount = max( 0.0, min( 1.0, $amount ) );
	$r      = (int) round( hexdec( substr( $hex, 1, 2 ) ) + ( 255 - hexdec( substr( $hex, 1, 2 ) ) ) * $amount );
	$g      = (int) round( hexdec( substr( $hex, 3, 2 ) ) + ( 255 - hexdec( substr( $hex, 3, 2 ) ) ) * $amount );
	$b      = (int) round( hexdec( substr( $hex, 5, 2 ) ) + ( 255 - hexdec( substr( $hex, 5, 2 ) ) ) * $amount );
	return sprintf( '#%02x%02x%02x', min( 255, $r ), min( 255, $g ), min( 255, $b ) );
}

/**
 * Read a Carbon theme option as string (ints cast for numeric selects).
 *
 * @param string $key Option key.
 * @return string
 */
function tonkla_appearance_option( string $key ): string {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return '';
	}
	return (string) carbon_get_theme_option( $key );
}

/**
 * Resolve one typography role from options (with legacy fallbacks).
 *
 * @param string               $role     Role slug.
 * @param array<string,string> $defaults Default role.
 * @param array<string,mixed>  $fonts    Font catalog.
 * @return array{font:string,size:string,weight:string,color:string,family:string}
 */
function tonkla_resolve_typography_role( string $role, array $defaults, array $fonts ): array {
	$font   = tonkla_appearance_option( "appearance_{$role}_font" );
	$size   = tonkla_appearance_option( "appearance_{$role}_size" );
	$weight = tonkla_appearance_option( "appearance_{$role}_weight" );
	$color  = tonkla_sanitize_hex_color( tonkla_appearance_option( "appearance_{$role}_color" ) );

	// Legacy keys (pre-roles).
	if ( 'body' === $role && '' === $font ) {
		$font = tonkla_appearance_option( 'appearance_font_body' );
	}
	if ( 'heading' === $role && '' === $font ) {
		$font = tonkla_appearance_option( 'appearance_font_heading' );
	}
	if ( 'body' === $role && '' === $size ) {
		$size = tonkla_appearance_option( 'appearance_base_size' );
	}
	if ( 'heading' === $role && '' === $weight ) {
		$weight = tonkla_appearance_option( 'appearance_heading_weight' );
	}

	if ( ! isset( $fonts[ $font ] ) ) {
		$font = $defaults['font'];
	}

	if ( 'heading' === $role ) {
		if ( ! isset( tonkla_heading_scale_options()[ $size ] ) ) {
			$size = $defaults['size'];
		}
	} elseif ( ! isset( tonkla_font_size_options()[ $size ] ) ) {
		$size = $defaults['size'];
	}

	if ( ! isset( tonkla_font_weight_options()[ $weight ] ) ) {
		$weight = $defaults['weight'];
	}

	return array(
		'font'   => $font,
		'size'   => $size,
		'weight' => $weight,
		'color'  => $color,
		'family' => $fonts[ $font ]['family'],
	);
}

/**
 * Enforce max 2 Google Font families across roles; later roles fall back to body/local.
 *
 * @param array<string,array{font:string,size:string,weight:string,color:string,family:string}> $roles Roles.
 * @param array<string,mixed>                                                                   $fonts Catalog.
 * @return array{roles:array,google_slugs:array<int,string>}
 */
function tonkla_enforce_google_font_limit( array $roles, array $fonts ): array {
	$google_slugs = array();
	$order        = array( 'body', 'heading', 'nav', 'caption' );

	foreach ( $order as $role ) {
		if ( ! isset( $roles[ $role ] ) ) {
			continue;
		}
		$slug = $roles[ $role ]['font'];
		if ( ! isset( $fonts[ $slug ] ) || 'google' !== $fonts[ $slug ]['source'] ) {
			continue;
		}
		if ( in_array( $slug, $google_slugs, true ) ) {
			continue;
		}
		if ( count( $google_slugs ) < 2 ) {
			$google_slugs[] = $slug;
			continue;
		}
		// Over limit: fall back to body font (or Sarabun).
		$fallback                       = $roles['body']['font'] ?? 'sarabun';
		$roles[ $role ]['font']         = $fallback;
		$roles[ $role ]['family']       = $fonts[ $fallback ]['family'] ?? '"Sarabun", sans-serif';
	}

	return array(
		'roles'        => $roles,
		'google_slugs' => $google_slugs,
	);
}

/**
 * Heading scale → H1/H2/H3 rem sizes.
 *
 * @param string $scale compact|scale|large.
 * @return array{h1:string,h2:string,h3:string}
 */
function tonkla_heading_scale_sizes( string $scale ): array {
	$map = array(
		'compact' => array(
			'h1' => '1.75rem',
			'h2' => '1.375rem',
			'h3' => '1.125rem',
		),
		'scale'   => array(
			'h1' => '2.25rem',
			'h2' => '1.5rem',
			'h3' => '1.25rem',
		),
		'large'   => array(
			'h1' => '2.75rem',
			'h2' => '1.875rem',
			'h3' => '1.375rem',
		),
	);
	return $map[ $scale ] ?? $map['scale'];
}

/**
 * Resolved appearance settings (sanitized).
 *
 * @return array<string,mixed>
 */
function tonkla_get_appearance(): array {
	$presets  = tonkla_color_presets();
	$fonts    = tonkla_font_catalog();
	$defaults = tonkla_typography_role_defaults();

	$preset_slug = 'emerald';
	$custom_hex  = '';
	$btn_radius  = '8';

	$surface_bg     = '';
	$surface_text   = '';
	$surface_muted  = '';
	$surface_link   = '';

	if ( function_exists( 'carbon_get_theme_option' ) ) {
		$raw_preset = tonkla_appearance_option( 'appearance_color_preset' );
		$custom_hex = tonkla_sanitize_hex_color( tonkla_appearance_option( 'appearance_custom_brand' ) );
		$btn_radius = tonkla_appearance_option( 'appearance_button_radius' );

		$surface_bg    = tonkla_sanitize_hex_color( tonkla_appearance_option( 'appearance_surface_bg' ) );
		$surface_text  = tonkla_sanitize_hex_color( tonkla_appearance_option( 'appearance_surface_text' ) );
		$surface_muted = tonkla_sanitize_hex_color( tonkla_appearance_option( 'appearance_surface_muted' ) );
		$surface_link  = tonkla_sanitize_hex_color( tonkla_appearance_option( 'appearance_surface_link' ) );

		if ( isset( $presets[ $raw_preset ] ) ) {
			$preset_slug = $raw_preset;
		}
	}

	if ( ! in_array( $btn_radius, array( '0', '4', '8', '12', '9999' ), true ) ) {
		$btn_radius = '8';
	}

	if ( 'custom' === $preset_slug && '' !== $custom_hex ) {
		$brand_hex   = $custom_hex;
		$brand_light = tonkla_hex_to_rgb_triplet( tonkla_hex_lighten( $custom_hex, 0.35 ) );
		$brand_base  = tonkla_hex_to_rgb_triplet( $custom_hex );
		$brand_dark  = tonkla_hex_to_rgb_triplet( tonkla_hex_darken( $custom_hex, 0.28 ) );
	} else {
		if ( 'custom' === $preset_slug ) {
			$preset_slug = 'emerald';
		}
		$p           = $presets[ $preset_slug ];
		$brand_hex   = $p['hex'];
		$brand_light = $p['light'];
		$brand_base  = $p['base'];
		$brand_dark  = $p['dark'];
	}

	$roles = array();
	foreach ( tonkla_typography_roles() as $role ) {
		$roles[ $role ] = tonkla_resolve_typography_role( $role, $defaults[ $role ], $fonts );
	}

	$limited = tonkla_enforce_google_font_limit( $roles, $fonts );
	$roles   = $limited['roles'];

	// Surface defaults (slate / brand-dark) when override empty.
	$bg_rgb     = '' !== $surface_bg ? tonkla_hex_to_rgb_triplet( $surface_bg ) : '248 250 252'; // slate-50
	$text_rgb   = '' !== $surface_text ? tonkla_hex_to_rgb_triplet( $surface_text ) : '30 41 59'; // slate-800
	$muted_rgb  = '' !== $surface_muted ? tonkla_hex_to_rgb_triplet( $surface_muted ) : '100 116 139'; // slate-500
	$link_rgb   = '' !== $surface_link ? tonkla_hex_to_rgb_triplet( $surface_link ) : $brand_dark;

	return array(
		'preset'         => $preset_slug,
		'brand_hex'      => $brand_hex,
		'brand_light'    => $brand_light,
		'brand_base'     => $brand_base,
		'brand_dark'     => $brand_dark,
		'button_radius'  => $btn_radius,
		'roles'          => $roles,
		'google_slugs'   => $limited['google_slugs'],
		// BC aliases used elsewhere.
		'body_font'      => $roles['body']['font'],
		'heading_font'   => $roles['heading']['font'],
		'base_size'      => $roles['body']['size'],
		'heading_weight' => $roles['heading']['weight'],
		'surface_bg'     => $bg_rgb,
		'surface_text'   => $text_rgb,
		'surface_muted'  => $muted_rgb,
		'surface_link'   => $link_rgb,
		'surface_bg_hex'     => $surface_bg,
		'surface_text_hex'   => $surface_text,
		'surface_muted_hex'  => $surface_muted,
		'surface_link_hex'   => $surface_link,
	);
}

/**
 * Space-separated RGB → comma-separated for WP admin theme vars.
 *
 * @param string $triplet e.g. "245 158 11".
 * @return string e.g. "245,158,11".
 */
function tonkla_rgb_triplet_commas( string $triplet ): string {
	$parts = preg_split( '/\s+/', trim( $triplet ) );
	if ( ! is_array( $parts ) || 3 !== count( $parts ) ) {
		return '16,185,129';
	}
	return implode( ',', $parts );
}

/**
 * Build :root CSS for the current appearance (front + admin).
 *
 * @return string
 */
function tonkla_appearance_css(): string {
	$a      = tonkla_get_appearance();
	$roles  = $a['roles'];
	$radius = '9999' === $a['button_radius'] ? '9999px' : $a['button_radius'] . 'px';
	$rgb_d  = tonkla_rgb_triplet_commas( $a['brand_dark'] );
	$scale  = tonkla_heading_scale_sizes( $roles['heading']['size'] );

	$body_color = '' !== $roles['body']['color']
		? tonkla_hex_to_rgb_triplet( $roles['body']['color'] )
		: $a['surface_text'];
	$head_color = '' !== $roles['heading']['color']
		? tonkla_hex_to_rgb_triplet( $roles['heading']['color'] )
		: $a['surface_text'];
	$nav_color  = '' !== $roles['nav']['color']
		? tonkla_hex_to_rgb_triplet( $roles['nav']['color'] )
		: '71 85 105'; // slate-600
	$cap_color  = '' !== $roles['caption']['color']
		? tonkla_hex_to_rgb_triplet( $roles['caption']['color'] )
		: $a['surface_muted'];

	$css  = ':root{';
	$css .= '--tk-brand-light:' . $a['brand_light'] . ';';
	$css .= '--tk-brand:' . $a['brand_base'] . ';';
	$css .= '--tk-brand-dark:' . $a['brand_dark'] . ';';
	$css .= '--tk-bg:' . $a['surface_bg'] . ';';
	$css .= '--tk-text:' . $a['surface_text'] . ';';
	$css .= '--tk-text-muted:' . $a['surface_muted'] . ';';
	$css .= '--tk-link:' . $a['surface_link'] . ';';
	$css .= '--tk-font-body:' . $roles['body']['family'] . ';';
	$css .= '--tk-font-heading:' . $roles['heading']['family'] . ';';
	$css .= '--tk-font-nav:' . $roles['nav']['family'] . ';';
	$css .= '--tk-font-caption:' . $roles['caption']['family'] . ';';
	$css .= '--tk-body-size:' . $roles['body']['size'] . 'px;';
	$css .= '--tk-body-weight:' . $roles['body']['weight'] . ';';
	$css .= '--tk-body-color:' . $body_color . ';';
	$css .= '--tk-heading-weight:' . $roles['heading']['weight'] . ';';
	$css .= '--tk-heading-color:' . $head_color . ';';
	$css .= '--tk-heading-h1:' . $scale['h1'] . ';';
	$css .= '--tk-heading-h2:' . $scale['h2'] . ';';
	$css .= '--tk-heading-h3:' . $scale['h3'] . ';';
	$css .= '--tk-nav-size:' . $roles['nav']['size'] . 'px;';
	$css .= '--tk-nav-weight:' . $roles['nav']['weight'] . ';';
	$css .= '--tk-nav-color:' . $nav_color . ';';
	$css .= '--tk-caption-size:' . $roles['caption']['size'] . 'px;';
	$css .= '--tk-caption-weight:' . $roles['caption']['weight'] . ';';
	$css .= '--tk-caption-color:' . $cap_color . ';';
	$css .= '--tk-base-size:' . $roles['body']['size'] . 'px;';
	$css .= '--tk-button-radius:' . $radius . ';';
	$css .= '--color-brand:rgb(var(--tk-brand));';
	$css .= '--tonkla-admin-accent:rgb(var(--tk-brand));';
	$css .= '--tonkla-admin-link:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-link-hover:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-menu-current:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-font:var(--tk-font-body);';
	$css .= '}';

	$css .= 'body.wp-admin{';
	$css .= '--tonkla-admin-accent:rgb(var(--tk-brand));';
	$css .= '--tonkla-admin-link:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-link-hover:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-menu-current:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-font:var(--tk-font-body);';
	$css .= '--wp-admin-theme-color:rgb(var(--tk-brand-dark));';
	$css .= '--wp-admin-theme-color--rgb:' . $rgb_d . ';';
	$css .= '--wp-admin-theme-color-darker-10:rgb(var(--tk-brand-dark));';
	$css .= '--wp-admin-theme-color-darker-10--rgb:' . $rgb_d . ';';
	$css .= '--wp-admin-theme-color-darker-20:rgb(var(--tk-brand-dark));';
	$css .= '--wp-admin-theme-color-darker-20--rgb:' . $rgb_d . ';';
	$css .= '}';

	// Body / page surface — set size on body only so Tailwind rem spacing stays stable.
	$css .= 'body{background-color:rgb(var(--tk-bg));color:rgb(var(--tk-body-color));font-family:var(--tk-font-body);font-weight:var(--tk-body-weight);font-size:var(--tk-base-size);}';
	$css .= '.font-sans{font-family:var(--tk-font-body);}';

	// Headings — family/weight on utility; color only on real heading tags so
	// .text-white on buttons / dark heroes is not overridden.
	$css .= '.font-heading{font-family:var(--tk-font-heading);font-weight:var(--tk-heading-weight);}';
	$css .= 'h1,h2,h3,h4,h5,h6{font-family:var(--tk-font-heading);font-weight:var(--tk-heading-weight);color:rgb(var(--tk-heading-color));}';
	$css .= 'h1{font-size:var(--tk-heading-h1);}';
	$css .= 'h2{font-size:var(--tk-heading-h2);}';
	$css .= 'h3{font-size:var(--tk-heading-h3);}';

	// Nav — skip filled CTAs (bg-* / text-white) so login button keeps contrast.
	$css .= '#navbar a:not([class*="bg-"]),#navbar button:not([class*="bg-"]),#mobile-menu a:not([class*="bg-"]),footer nav a,footer .menu a{font-family:var(--tk-font-nav);font-size:var(--tk-nav-size);font-weight:var(--tk-nav-weight);}';
	$css .= '#navbar a:not([class*="bg-"]):not([class*="text-white"]),#mobile-menu a:not([class*="bg-"]):not([class*="text-white"]){color:rgb(var(--tk-nav-color));}';
	$css .= '#navbar a:not([class*="bg-"]):not([class*="text-white"]):hover,#mobile-menu a:not([class*="bg-"]):not([class*="text-white"]):hover{color:rgb(var(--tk-brand));}';

	// Caption / meta.
	$css .= '.text-caption{font-family:var(--tk-font-caption);font-size:var(--tk-caption-size);font-weight:var(--tk-caption-weight);color:rgb(var(--tk-caption-color));}';

	// Links (content).
	$css .= '.site-content a:not([class]),.prose a,article .entry-content a{color:rgb(var(--tk-link));}';
	$css .= '.site-content a:not([class]):hover,.prose a:hover,article .entry-content a:hover{color:rgb(var(--tk-brand));}';

	$css .= 'a.bg-brand,button.bg-brand,.bg-brand.rounded-lg,.bg-brand.rounded-xl,#pdpa-accept-btn,#back-to-top{border-radius:var(--tk-button-radius);}';

	$css .= 'body.wp-admin .button-primary,body.wp-admin .wrap .page-title-action,body.wp-admin .wp-core-ui .button-primary{background:rgb(var(--tk-brand))!important;border-color:rgb(var(--tk-brand-dark))!important;}';
	$css .= 'body.wp-admin .button-primary:hover,body.wp-admin .button-primary:focus,body.wp-admin .wrap .page-title-action:hover,body.wp-admin .wrap .page-title-action:focus{background:rgb(var(--tk-brand-dark))!important;border-color:rgb(var(--tk-brand-dark))!important;}';

	return $css;
}

/**
 * Google Fonts URL for selected role fonts (max 2 families).
 *
 * @return string Empty if only local/system fonts.
 */
function tonkla_google_fonts_url(): string {
	$a     = tonkla_get_appearance();
	$fonts = tonkla_font_catalog();
	$parts = array();
	$seen  = array();

	$slugs = $a['google_slugs'] ?? array();
	if ( empty( $slugs ) ) {
		// Fallback: walk roles in priority order.
		foreach ( array( 'body', 'heading', 'nav', 'caption' ) as $role ) {
			$slug = $a['roles'][ $role ]['font'] ?? '';
			if ( isset( $fonts[ $slug ] ) && 'google' === $fonts[ $slug ]['source'] && ! in_array( $slug, $slugs, true ) ) {
				$slugs[] = $slug;
			}
			if ( count( $slugs ) >= 2 ) {
				break;
			}
		}
	}

	foreach ( $slugs as $slug ) {
		if ( ! isset( $fonts[ $slug ] ) || 'google' !== $fonts[ $slug ]['source'] ) {
			continue;
		}
		$key = $fonts[ $slug ]['google'] ?? '';
		if ( '' === $key || isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$parts[]       = 'family=' . $key;
	}

	if ( empty( $parts ) ) {
		return '';
	}

	return 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap';
}

/**
 * Local font family slugs still referenced by appearance roles.
 *
 * @return array<int,string> e.g. array( 'prompt', 'sarabun' ).
 */
function tonkla_local_font_slugs(): array {
	$a      = tonkla_get_appearance();
	$fonts  = tonkla_font_catalog();
	$needed = array();
	foreach ( $a['roles'] as $role ) {
		$slug = $role['font'] ?? '';
		if ( isset( $fonts[ $slug ] ) && 'local' === $fonts[ $slug ]['source'] ) {
			$needed[ $slug ] = true;
		}
	}
	return array_keys( $needed );
}

/**
 * Whether any local Prompt/Sarabun stylesheet is still needed.
 *
 * @return bool
 */
function tonkla_needs_local_fonts(): bool {
	return array() !== tonkla_local_font_slugs();
}

/**
 * Attach appearance CSS after main.css so :root tokens win cascade order.
 * Early wp_head print covers FOUC before the stylesheet arrives.
 */
function tonkla_print_appearance_css(): void {
	$css = tonkla_appearance_css();
	echo '<style id="tonkla-appearance">' . $css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS built from sanitized options only.
}
add_action( 'wp_head', 'tonkla_print_appearance_css', 2 );
add_action( 'admin_head', 'tonkla_print_appearance_css', 100 );

/**
 * Re-apply appearance after tonkla-output (fixes main.css winning over early <style>).
 */
function tonkla_enqueue_appearance_inline_css(): void {
	if ( ! wp_style_is( 'tonkla-output', 'enqueued' ) && ! wp_style_is( 'tonkla-output', 'done' ) ) {
		return;
	}
	wp_add_inline_style( 'tonkla-output', tonkla_appearance_css() );
}
add_action( 'wp_enqueue_scripts', 'tonkla_enqueue_appearance_inline_css', 40 );

/**
 * Enqueue Google Fonts when selected (local fonts handled in tonkla_scripts).
 */
function tonkla_enqueue_appearance_fonts(): void {
	$google = tonkla_google_fonts_url();
	if ( '' === $google ) {
		return;
	}
	wp_enqueue_style( 'tonkla-google-fonts', $google, array(), null );
}
add_action( 'wp_enqueue_scripts', 'tonkla_enqueue_appearance_fonts', 20 );
add_action( 'admin_enqueue_scripts', 'tonkla_enqueue_appearance_fonts', 20 );

/**
 * Preconnect to Google Fonts when loading remote families.
 */
function tonkla_font_preconnect(): void {
	if ( '' === tonkla_google_fonts_url() ) {
		return;
	}
	echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
}
add_action( 'wp_head', 'tonkla_font_preconnect', 1 );
add_action( 'admin_head', 'tonkla_font_preconnect', 1 );

/**
 * Brand hex for JS (newsletter alerts, etc.).
 *
 * @return string
 */
function tonkla_brand_hex(): string {
	return tonkla_get_appearance()['brand_hex'];
}

/**
 * HTML swatches for the Appearance tab help text.
 *
 * @return string
 */
function tonkla_color_preset_swatches_html(): string {
	$rows = array();
	foreach ( tonkla_color_presets() as $slug => $preset ) {
		if ( 'custom' === $slug ) {
			continue;
		}
		$rows[] = sprintf(
			'<span style="display:inline-flex;align-items:center;gap:6px;margin:0 12px 8px 0;font-size:12px;color:#334155;"><span style="width:14px;height:14px;border-radius:4px;background:%1$s;border:1px solid #cbd5e1;"></span>%2$s</span>',
			esc_attr( $preset['hex'] ),
			esc_html( $preset['label'] )
		);
	}
	return '<p style="margin:0 0 8px;color:#64748b;font-size:13px;">' . esc_html__( 'พรีวิวพรีเซ็ตสีหลัก', 'tonkla' ) . '</p><div>' . implode( '', $rows ) . '</div>';
}
