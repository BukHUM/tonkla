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
				'label' => __( 'Hardware', 'it-warranty-tracker' ),
			),
			array(
				'slug'  => 'system',
				'label' => __( 'System', 'it-warranty-tracker' ),
			),
			array(
				'slug'  => 'software',
				'label' => __( 'Software', 'it-warranty-tracker' ),
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
