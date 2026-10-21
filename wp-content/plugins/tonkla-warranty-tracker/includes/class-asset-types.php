<?php
/**
 * Configurable asset types.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Reads / validates asset type definitions from settings.
 */
final class Asset_Types {

	/**
	 * Default built-in types.
	 *
	 * @return array<int,array{slug:string,label:string}>
	 */
	public static function defaults(): array {
		return array(
			array(
				'slug'  => 'hardware',
				'label' => __( 'Hardware', 'tonkla-warranty-tracker' ),
			),
			array(
				'slug'  => 'system',
				'label' => __( 'System', 'tonkla-warranty-tracker' ),
			),
			array(
				'slug'  => 'software',
				'label' => __( 'Software', 'tonkla-warranty-tracker' ),
			),
		);
	}

	/**
	 * All configured types (at least defaults).
	 *
	 * @return array<int,array{slug:string,label:string}>
	 */
	public static function all(): array {
		$settings = Settings::get();
		$stored   = $settings['asset_types'] ?? null;
		if ( ! is_array( $stored ) || empty( $stored ) ) {
			return self::defaults();
		}

		$types = array();
		foreach ( $stored as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$slug  = sanitize_key( (string) ( $row['slug'] ?? '' ) );
			$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
			if ( '' === $slug || '' === $label || strlen( $slug ) > 30 ) {
				continue;
			}
			$types[ $slug ] = array(
				'slug'  => $slug,
				'label' => $label,
			);
		}

		return ! empty( $types ) ? array_values( $types ) : self::defaults();
	}

	/**
	 * Slug list.
	 *
	 * @return array<int,string>
	 */
	public static function slugs(): array {
		return array_map(
			static fn( array $t ): string => $t['slug'],
			self::all()
		);
	}

	/**
	 * Label for a slug (falls back to slug).
	 *
	 * @param string $slug Type slug.
	 */
	public static function label( string $slug ): string {
		$slug = sanitize_key( $slug );
		foreach ( self::all() as $type ) {
			if ( $type['slug'] === $slug ) {
				return $type['label'];
			}
		}
		return $slug;
	}

	/**
	 * Icon kind for sticker / UI (print-safe SVG key).
	 *
	 * @param string $slug Type slug.
	 * @return string One of: server, laptop, software, network, printer, device.
	 */
	public static function icon_kind( string $slug ): string {
		$slug  = sanitize_key( $slug );
		$hay   = strtolower( $slug . ' ' . self::label( $slug ) );
		$rules = array(
			'server'   => array( 'server', 'hardware', 'rack', 'เซิร์ฟเวอร์' ),
			'laptop'   => array( 'notebook', 'laptop', 'pc', 'computer', 'desktop', 'โน้ตบุ๊ก', 'คอมพิวเตอร์' ),
			'software' => array( 'software', 'license', 'app', 'ซอฟต์แวร์' ),
			'network'  => array( 'network', 'switch', 'router', 'firewall', 'เครือข่าย' ),
			'printer'  => array( 'printer', 'print', 'เครื่องพิมพ์' ),
		);
		foreach ( $rules as $kind => $needles ) {
			foreach ( $needles as $needle ) {
				if ( false !== strpos( $hay, $needle ) ) {
					return $kind;
				}
			}
		}
		return 'device';
	}

	/**
	 * Inline SVG for sticker type badge (print-friendly).
	 *
	 * @param string $slug Type slug.
	 */
	public static function icon_svg( string $slug ): string {
		$kind = self::icon_kind( $slug );
		$svgs = array(
			'server'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4 3h16a1 1 0 0 1 1 1v5H3V4a1 1 0 0 1 1-1zm0 8h17v5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-5zm1 1.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm0-8a1 1 0 1 0 0 2 1 1 0 0 0 0-2zM3 19h18v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-1z"/></svg>',
			'laptop'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9H4V5zm-2 11h20v1a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-1z"/></svg>',
			'software' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8.7 7.3 4 12l4.7 4.7 1.4-1.4L6.8 12l3.3-3.3-1.4-1.4zm6.6 0-1.4 1.4L17.2 12l-3.3 3.3 1.4 1.4L20 12l-4.7-4.7z"/></svg>',
			'network'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a3 3 0 0 1 1 5.83V10h3a3 3 0 1 1 0 2h-3v2.17A3 3 0 1 1 11 17v-2.17A3.001 3.001 0 0 1 9 10h3V7.83A3 3 0 0 1 12 2z"/></svg>',
			'printer'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M7 3h10v4H7V3zm-3 6h16a2 2 0 0 1 2 2v5h-4v5H7v-5H3v-5a2 2 0 0 1 2-2zm4 7v3h8v-3H8z"/></svg>',
			'device'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6 3h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3l1 2H8l1-2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm0 2v10h12V5H6z"/></svg>',
		);
		return $svgs[ $kind ] ?? $svgs['device'];
	}

	/**
	 * Whether slug is allowed.
	 *
	 * @param string $slug Type slug.
	 */
	public static function is_valid( string $slug ): bool {
		return in_array( sanitize_key( $slug ), self::slugs(), true );
	}

	/**
	 * First / default slug.
	 */
	public static function default_slug(): string {
		$slugs = self::slugs();
		return $slugs[0] ?? 'hardware';
	}

	/**
	 * Sanitize posted type rows and sync empty spec templates for new slugs.
	 *
	 * @param array<int,mixed> $rows Posted rows.
	 * @return array<int,array{slug:string,label:string}>
	 */
	public static function sanitize_list( array $rows ): array {
		$types = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$slug  = sanitize_key( (string) ( $row['slug'] ?? '' ) );
			$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
			if ( '' === $slug ) {
				// Derive slug from label if only label provided.
				$slug = sanitize_key( $label );
			}
			if ( '' === $slug || '' === $label || strlen( $slug ) > 30 ) {
				continue;
			}
			$types[ $slug ] = array(
				'slug'  => $slug,
				'label' => $label,
			);
		}

		$list = ! empty( $types ) ? array_values( $types ) : self::defaults();
		self::sync_spec_templates( $list );
		return $list;
	}

	/**
	 * Ensure each type has a spec template entry.
	 *
	 * @param array<int,array{slug:string,label:string}> $types Types.
	 */
	private static function sync_spec_templates( array $types ): void {
		$templates = get_option( 'itwt_spec_templates', array() );
		if ( ! is_array( $templates ) ) {
			$templates = array();
		}

		$changed = false;
		foreach ( $types as $type ) {
			$slug = $type['slug'];
			if ( ! isset( $templates[ $slug ] ) || ! is_array( $templates[ $slug ] ) ) {
				$templates[ $slug ] = array(
					array(
						'key'    => '',
						'value'  => '',
						'public' => true,
					),
				);
				$changed = true;
			}
		}

		if ( $changed || false === get_option( 'itwt_spec_templates', false ) ) {
			update_option( 'itwt_spec_templates', $templates, false );
		}
	}
}
