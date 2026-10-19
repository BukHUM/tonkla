<?php
/**
 * Build a Font Awesome CSS subset for the Tonkla front end.
 *
 * Extracts base utilities + @font-face + only the .fa-{name}:before rules needed.
 * Avoids naive }-splitting (that previously nested icons inside prefers-reduced-motion).
 *
 * Usage: php bin/build-fa-subset.php
 *
 * @package Tonkla
 */

declare(strict_types=1);

$theme_dir  = dirname( __DIR__ );
$all_css    = $theme_dir . '/assets/vendor/fontawesome/css/all.min.css';
$out_css    = $theme_dir . '/assets/vendor/fontawesome/css/fa-subset.min.css';
$icons_json = $theme_dir . '/assets/data/fa-icons.json';

if ( ! is_readable( $all_css ) ) {
	fwrite( STDERR, "Missing all.min.css\n" );
	exit( 1 );
}

$icon_names = array();

$catalog = json_decode( (string) file_get_contents( $icons_json ), true );
if ( is_array( $catalog ) ) {
	foreach ( $catalog as $row ) {
		if ( ! empty( $row['n'] ) ) {
			$icon_names[ (string) $row['n'] ] = true;
		}
	}
}

foreach ( array(
	'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down',
	'angle-right', 'angle-left', 'angle-up', 'angle-down',
	'arrow-right', 'arrow-up', 'arrow-left', 'arrow-down',
	'user-lock', 'cookie-bite', 'spinner', 'list-ul', 'folder-open',
	'envelope-open', 'rectangle-ad', 'check', 'globe', 'magnifying-glass',
	'mobile-screen', 'mobile-screen-button', 'laptop-code', 'share-nodes',
	'server', 'cloud', 'network-wired', 'code', 'circle',
	'facebook', 'facebook-f', 'x-twitter', 'twitter', 'instagram', 'line',
	'linkedin', 'linkedin-in', 'youtube',
) as $extra ) {
	$icon_names[ $extra ] = true;
}

$skip_dirs = array( '/vendor/', '/node_modules/', '/bin/' );
$ext_ok    = array( 'php' => true, 'js' => true, 'css' => true );
$iterator  = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS )
);

foreach ( $iterator as $file ) {
	/** @var SplFileInfo $file */
	if ( ! $file->isFile() ) {
		continue;
	}
	$path = str_replace( '\\', '/', $file->getPathname() );
	foreach ( $skip_dirs as $needle ) {
		if ( false !== strpos( $path, $needle ) ) {
			continue 2;
		}
	}
	$ext = strtolower( $file->getExtension() );
	if ( ! isset( $ext_ok[ $ext ] ) ) {
		continue;
	}
	$content = (string) file_get_contents( $path );
	if ( ! preg_match_all( '/\bfa-([a-z0-9-]+)\b/', $content, $matches ) ) {
		continue;
	}
	foreach ( $matches[1] as $name ) {
		if ( is_utility_token( $name ) ) {
			continue;
		}
		$icon_names[ $name ] = true;
	}
}

ksort( $icon_names );
$source = (string) file_get_contents( $all_css );

// 1) License comment.
$out = '';
if ( preg_match( '/^\/\*\!.*?\*\//s', $source, $m ) ) {
	$out .= $m[0];
}

// 2) Base / utility blocks that must always ship (not icon glyphs).
$base_patterns = array(
	// Core .fa / family selectors through size + spin utilities (before first icon :before).
	'/\.fa\{[^}]+\}/',
	'/\.fa,\.fa-brands[\s\S]*?text-rendering:auto\}/',
	'/\.fa-classic,\.fa-regular,\.fa-solid[\s\S]*?Font Awesome 6 Free"\}/',
	'/\.fa-brands,\.fab\{[^}]+\}/',
	'/\.fa-1x\{[^}]+\}/',
	'/\.fa-2x\{[^}]+\}/',
	'/\.fa-3x\{[^}]+\}/',
	'/\.fa-4x\{[^}]+\}/',
	'/\.fa-5x\{[^}]+\}/',
	'/\.fa-xs\{[^}]+\}/',
	'/\.fa-sm\{[^}]+\}/',
	'/\.fa-lg\{[^}]+\}/',
	'/\.fa-fw\{[^}]+\}/',
	'/\.fa-spin\{[^}]+\}/',
	'/\.fa-pulse,\.fa-spin-pulse\{[^}]+\}/',
	'/\.fa-shake,\.fa-spin\{[^}]+\}/',
	'/@keyframes fa-spin\{[\s\S]*?\}\}/',
	'/\.fa-sr-only[\s\S]*?border-width:0\}/',
);

$seen = array();
foreach ( $base_patterns as $pattern ) {
	if ( preg_match_all( $pattern, $source, $matches ) ) {
		foreach ( $matches[0] as $block ) {
			$key = md5( $block );
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$out         .= $block;
		}
	}
}

// 3) Icon :before rules — match selector lists that include .fa-{name}
$icon_count = 0;
foreach ( array_keys( $icon_names ) as $name ) {
	$quoted = preg_quote( $name, '/' );
	// e.g. .fa-server:before{content:"\f233"} or .fa-home:before,.fa-house:before{...}
	$pattern = '/\.fa-' . $quoted . '(?![a-z0-9-])(?::before)?(?:,\.fa-[a-z0-9-]+(?::before)?)*\{[^}]+\}/';
	if ( ! preg_match_all( $pattern, $source, $matches ) ) {
		// Broader: any rule whose selectors mention .fa-name:before
		$pattern = '/(?:[^\{]*\.fa-' . $quoted . '(?![a-z0-9-])[^\{]*)\{[^}]+\}/';
		if ( ! preg_match_all( $pattern, $source, $matches ) ) {
			continue;
		}
	}
	foreach ( $matches[0] as $block ) {
		// Only keep glyph rules (must set content).
		if ( false === strpos( $block, 'content:' ) ) {
			continue;
		}
		$key = md5( $block );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out         .= $block;
		++$icon_count;
	}
}

// 4) CSS variables + @font-face for Free/Brands (skip v4compatibility).
if ( preg_match_all( '/(?::host,:root|:root|:host)\{--fa[^}]+\}/', $source, $matches ) ) {
	foreach ( $matches[0] as $block ) {
		$key = md5( $block );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out         .= $block;
	}
}

if ( preg_match_all( '/@font-face\{[^}]+\}/', $source, $matches ) ) {
	foreach ( $matches[0] as $block ) {
		if ( false !== strpos( $block, 'fa-v4compatibility' ) ) {
			continue;
		}
		// Drop ttf fallback — keep woff2 only.
		$block = preg_replace(
			'/,?url\(\.\.\/webfonts\/[^)]+\.ttf\)\s*format\("truetype"\)/i',
			'',
			$block
		);
		$block = preg_replace( '/format\("woff2"\),/', 'format("woff2")', $block ?? '' );
		$block = preg_replace( '/,\s*;/', ';', $block ?? '' );
		$key   = md5( $block );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out         .= $block;
	}
}

// Family weight helpers that follow @font-face in upstream.
foreach ( array(
	'/\.fa-brands,\.fab\{font-weight:400\}/',
	'/\.fa-regular,\.far\{font-weight:400\}/',
	'/\.fa-solid,\.fas\{font-weight:900\}/',
) as $pattern ) {
	if ( preg_match( $pattern, $source, $m ) ) {
		$key = md5( $m[0] );
		if ( ! isset( $seen[ $key ] ) ) {
			$seen[ $key ] = true;
			$out         .= $m[0];
		}
	}
}

$header = '/*! Tonkla FA subset — generated ' . gmdate( 'c' ) . ' — ' . count( $icon_names ) . " icons */\n";
file_put_contents( $out_css, $header . $out );

$src_size = filesize( $all_css );
$out_size = filesize( $out_css );

echo 'Icons catalogued: ' . count( $icon_names ) . "\n";
echo "Icon rules kept: {$icon_count}\n";
echo 'all.min.css:  ' . number_format( (int) $src_size ) . " bytes\n";
echo 'fa-subset:    ' . number_format( (int) $out_size ) . " bytes\n";
echo 'Saved:        ' . number_format( (int) $src_size - (int) $out_size ) . " bytes\n";
echo "Wrote: {$out_css}\n";

// Sanity: must NOT nest .fa-server inside prefers-reduced-motion.
$built = (string) file_get_contents( $out_css );
if ( preg_match( '/prefers-reduced-motion[\s\S]*\.fa-server:before/', $built ) ) {
	fwrite( STDERR, "ERROR: icons nested inside prefers-reduced-motion — abort\n" );
	exit( 1 );
}
if ( false === strpos( $built, '.fa-server:before' ) || false === strpos( $built, '.fa-arrow-up:before' ) ) {
	fwrite( STDERR, "ERROR: missing critical icon rules\n" );
	exit( 1 );
}
if ( false !== strpos( $built, 'format("woff2"),;' ) ) {
	fwrite( STDERR, "ERROR: broken font-face trailing comma\n" );
	exit( 1 );
}

echo "Sanity checks passed.\n";

/**
 * @param string $name Token after fa-.
 */
function is_utility_token( string $name ): bool {
	static $utils = array(
		'solid' => true, 'regular' => true, 'brands' => true, 'classic' => true, 'sharp' => true,
		'1x' => true, '2x' => true, '3x' => true, '4x' => true, '5x' => true,
		'lg' => true, 'sm' => true, 'xs' => true, 'fw' => true,
		'spin' => true, 'pulse' => true, 'beat' => true, 'fade' => true, 'bounce' => true, 'flip' => true, 'shake' => true,
		'rotate-90' => true, 'rotate-180' => true, 'rotate-270' => true,
		'flip-horizontal' => true, 'flip-vertical' => true,
		'stack' => true, 'ul' => true, 'li' => true,
		'sr-only' => true, 'sr-only-focusable' => true,
	);
	return isset( $utils[ $name ] );
}
