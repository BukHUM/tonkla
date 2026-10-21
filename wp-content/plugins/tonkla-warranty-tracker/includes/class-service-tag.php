<?php
/**
 * Service Tag generator and normalizer.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Generates and normalizes Service Tags.
 */
final class Service_Tag {

	/**
	 * Base32 alphabet without O, 0, I, 1.
	 */
	private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

	/**
	 * Generate a unique 7-character service tag.
	 *
	 * @param callable|null $exists_callback Receives tag string, returns true if taken.
	 * @return string|null Null if unable to generate unique tag.
	 */
	public static function generate( ?callable $exists_callback = null ): ?string {
		for ( $attempt = 0; $attempt < 5; $attempt++ ) {
			$tag = self::random_tag();
			if ( null === $exists_callback || ! $exists_callback( $tag ) ) {
				return $tag;
			}
		}
		return null;
	}

	/**
	 * Normalize tag or serial for storage/lookup.
	 *
	 * @param string $value Raw input.
	 */
	public static function normalize( string $value ): string {
		$value = wp_strip_all_tags( $value );
		$value = preg_replace( '/\s+/', '', $value ) ?? '';
		return strtoupper( $value );
	}

	/**
	 * Create one random tag.
	 */
	private static function random_tag(): string {
		$alphabet = self::ALPHABET;
		$max      = strlen( $alphabet ) - 1;
		$tag      = '';
		for ( $i = 0; $i < 7; $i++ ) {
			$tag .= $alphabet[ random_int( 0, $max ) ];
		}
		return $tag;
	}
}
