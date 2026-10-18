<?php
/**
 * Front-end appearance: color presets, Google Fonts, CSS custom properties.
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
		'sarabun'     => array(
			'label'   => __( 'Sarabun (ค่าเริ่มต้น — โฮสต์ในธีม)', 'tonkla' ),
			'family'  => '"Sarabun", sans-serif',
			'source'  => 'local',
			'weights' => array( 400, 600, 700 ),
		),
		'prompt'      => array(
			'label'   => __( 'Prompt (ค่าเริ่มต้นหัวข้อ — โฮสต์ในธีม)', 'tonkla' ),
			'family'  => '"Prompt", sans-serif',
			'source'  => 'local',
			'weights' => array( 400, 600, 700 ),
		),
		'noto_sans_thai' => array(
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
		'kanit'       => array(
			'label'   => 'Kanit',
			'family'  => '"Kanit", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Kanit:wght@400;600;700',
		),
		'chakra_petch' => array(
			'label'   => 'Chakra Petch',
			'family'  => '"Chakra Petch", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Chakra+Petch:wght@400;600;700',
		),
		'anuphan'     => array(
			'label'   => 'Anuphan',
			'family'  => '"Anuphan", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Anuphan:wght@400;600;700',
		),
		'inter'       => array(
			'label'   => __( 'Inter (เน้นละติน — ไทยใช้ฟอนต์สำรอง)', 'tonkla' ),
			'family'  => '"Inter", "Sarabun", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Inter:wght@400;600;700',
		),
		'google_sans' => array(
			'label'   => __( 'Google Sans (เน้นละติน — ไทยใช้ฟอนต์สำรอง)', 'tonkla' ),
			'family'  => '"Google Sans", "Sarabun", sans-serif',
			'source'  => 'google',
			'weights' => array( 400, 600, 700 ),
			'google'  => 'Google+Sans:wght@400;600;700',
		),
		'system'      => array(
			'label'   => __( 'ฟอนต์ระบบ (เร็วที่สุด)', 'tonkla' ),
			'family'  => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
			'source'  => 'system',
			'weights' => array(),
		),
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
 * Resolved appearance settings (sanitized).
 *
 * @return array{
 *   preset:string,
 *   brand_hex:string,
 *   brand_light:string,
 *   brand_base:string,
 *   brand_dark:string,
 *   body_font:string,
 *   heading_font:string,
 *   base_size:string,
 *   heading_weight:string,
 *   button_radius:string
 * }
 */
function tonkla_get_appearance(): array {
	$presets = tonkla_color_presets();
	$fonts   = tonkla_font_catalog();

	$preset_slug = 'emerald';
	$body_slug   = 'sarabun';
	$head_slug   = 'prompt';
	$custom_hex  = '';
	$base_size   = '16';
	$head_weight = '600';
	$btn_radius  = '8';

	if ( function_exists( 'carbon_get_theme_option' ) ) {
		$raw_preset  = (string) carbon_get_theme_option( 'appearance_color_preset' );
		$raw_body    = (string) carbon_get_theme_option( 'appearance_font_body' );
		$raw_head    = (string) carbon_get_theme_option( 'appearance_font_heading' );
		$custom_hex  = tonkla_sanitize_hex_color( (string) carbon_get_theme_option( 'appearance_custom_brand' ) );
		// Carbon may return ints for select values that look numeric.
		$base_size   = (string) carbon_get_theme_option( 'appearance_base_size' );
		$head_weight = (string) carbon_get_theme_option( 'appearance_heading_weight' );
		$btn_radius  = (string) carbon_get_theme_option( 'appearance_button_radius' );

		if ( isset( $presets[ $raw_preset ] ) ) {
			$preset_slug = $raw_preset;
		}
		if ( isset( $fonts[ $raw_body ] ) ) {
			$body_slug = $raw_body;
		}
		if ( isset( $fonts[ $raw_head ] ) ) {
			$head_slug = $raw_head;
		}
	}

	if ( ! in_array( $base_size, array( '15', '16', '17', '18' ), true ) ) {
		$base_size = '16';
	}
	if ( ! in_array( $head_weight, array( '500', '600', '700' ), true ) ) {
		$head_weight = '600';
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

	return array(
		'preset'         => $preset_slug,
		'brand_hex'      => $brand_hex,
		'brand_light'    => $brand_light,
		'brand_base'     => $brand_base,
		'brand_dark'     => $brand_dark,
		'body_font'      => $body_slug,
		'heading_font'   => $head_slug,
		'base_size'      => $base_size,
		'heading_weight' => $head_weight,
		'button_radius'  => $btn_radius,
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
	$fonts  = tonkla_font_catalog();
	$body   = $fonts[ $a['body_font'] ]['family'] ?? '"Sarabun", sans-serif';
	$head   = $fonts[ $a['heading_font'] ]['family'] ?? '"Prompt", sans-serif';
	$radius = '9999' === $a['button_radius'] ? '9999px' : $a['button_radius'] . 'px';
	$rgb_d  = tonkla_rgb_triplet_commas( $a['brand_dark'] );

	$css  = ':root{';
	$css .= '--tk-brand-light:' . $a['brand_light'] . ';';
	$css .= '--tk-brand:' . $a['brand_base'] . ';';
	$css .= '--tk-brand-dark:' . $a['brand_dark'] . ';';
	$css .= '--tk-font-body:' . $body . ';';
	$css .= '--tk-font-heading:' . $head . ';';
	$css .= '--tk-base-size:' . $a['base_size'] . 'px;';
	$css .= '--tk-heading-weight:' . $a['heading_weight'] . ';';
	$css .= '--tk-button-radius:' . $radius . ';';
	$css .= '--color-brand:rgb(var(--tk-brand));';
	/* Sync admin chrome tokens (admin-chrome.css) to the selected preset. */
	$css .= '--tonkla-admin-accent:rgb(var(--tk-brand));';
	$css .= '--tonkla-admin-link:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-link-hover:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-menu-current:rgb(var(--tk-brand-dark));';
	$css .= '--tonkla-admin-font:var(--tk-font-body);';
	$css .= '}';
	/* body.wp-admin beats :root — admin-chrome.css also sets these on body. */
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
	$css .= 'html{font-size:var(--tk-base-size);}';
	$css .= 'html,body{font-family:var(--tk-font-body);}';
	$css .= '.font-heading,h1,h2,h3,h4,h5,h6{font-family:var(--tk-font-heading);font-weight:var(--tk-heading-weight);}';
	$css .= 'a.bg-brand,button.bg-brand,.bg-brand.rounded-lg,.bg-brand.rounded-xl,#pdpa-accept-btn,#back-to-top{border-radius:var(--tk-button-radius);}';
	/* Primary admin buttons + current menu (beat hardcoded green hover in admin-chrome). */
	$css .= 'body.wp-admin .button-primary,body.wp-admin .wrap .page-title-action,body.wp-admin .wp-core-ui .button-primary{background:rgb(var(--tk-brand))!important;border-color:rgb(var(--tk-brand-dark))!important;}';
	$css .= 'body.wp-admin .button-primary:hover,body.wp-admin .button-primary:focus,body.wp-admin .wrap .page-title-action:hover,body.wp-admin .wrap .page-title-action:focus{background:rgb(var(--tk-brand-dark))!important;border-color:rgb(var(--tk-brand-dark))!important;}';

	return $css;
}

/**
 * Google Fonts URL for selected fonts (max 2 families).
 *
 * @return string Empty if only local/system fonts.
 */
function tonkla_google_fonts_url(): string {
	$a     = tonkla_get_appearance();
	$fonts = tonkla_font_catalog();
	$parts = array();
	$seen  = array();

	foreach ( array( $a['body_font'], $a['heading_font'] ) as $slug ) {
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
 * Whether local Prompt/Sarabun stylesheet is still needed.
 *
 * @return bool
 */
function tonkla_needs_local_fonts(): bool {
	$a     = tonkla_get_appearance();
	$fonts = tonkla_font_catalog();
	foreach ( array( $a['body_font'], $a['heading_font'] ) as $slug ) {
		if ( isset( $fonts[ $slug ] ) && 'local' === $fonts[ $slug ]['source'] ) {
			return true;
		}
	}
	return false;
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
