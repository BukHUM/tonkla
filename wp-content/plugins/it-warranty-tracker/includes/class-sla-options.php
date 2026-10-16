<?php
/**
 * Canonical SLA / service condition options.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Checkbox options for contract sla_tier (stored as "A / B / C").
 */
final class Sla_Options {

	/**
	 * Max length for stored sla_tier string.
	 */
	private const MAX_LENGTH = 255;

	/**
	 * Allowed values (value => label). Values are stored in DB.
	 *
	 * @return array<string,string>
	 */
	public static function options(): array {
		return array(
			'On-Site 24x7' => __( 'On-Site 24x7', 'it-warranty-tracker' ),
			'On-Site 8x5'  => __( 'On-Site 8x5', 'it-warranty-tracker' ),
			'NBD'          => __( 'NBD (Next Business Day)', 'it-warranty-tracker' ),
			'Carry-in'     => __( 'Carry-in', 'it-warranty-tracker' ),
		);
	}

	/**
	 * Split stored string into parts.
	 *
	 * @param string $stored DB value.
	 * @return array<int,string>
	 */
	public static function split( string $stored ): array {
		$stored = trim( $stored );
		if ( '' === $stored ) {
			return array();
		}

		$parts = preg_split( '/\s*[\/,|]\s*/u', $stored ) ?: array();
		return array_values(
			array_filter(
				array_map(
					static function ( $p ) {
						return trim( (string) $p );
					},
					$parts
				),
				static function ( $p ) {
					return '' !== $p;
				}
			)
		);
	}

	/**
	 * Parse stored string into known option values (canonical order).
	 *
	 * @param string $stored DB value.
	 * @return array<int,string>
	 */
	public static function parse( string $stored ): array {
		$parts   = self::split( $stored );
		$allowed = array_keys( self::options() );
		$picked  = array();
		foreach ( $allowed as $value ) {
			foreach ( $parts as $part ) {
				if ( 0 === strcasecmp( $value, $part ) ) {
					$picked[] = $value;
					break;
				}
			}
		}
		return $picked;
	}

	/**
	 * Custom (non-canonical) parts from stored string, joined for the text field.
	 *
	 * @param string $stored DB value.
	 */
	public static function custom_text( string $stored ): string {
		$parts   = self::split( $stored );
		$allowed = array_keys( self::options() );
		$custom  = array();
		foreach ( $parts as $part ) {
			$is_known = false;
			foreach ( $allowed as $value ) {
				if ( 0 === strcasecmp( $value, $part ) ) {
					$is_known = true;
					break;
				}
			}
			if ( ! $is_known ) {
				$custom[] = $part;
			}
		}
		return implode( ' / ', $custom );
	}

	/**
	 * Encode selected known values + optional custom text for storage.
	 *
	 * @param array<int,string> $selected Selected option values.
	 * @param string            $custom   Free-text extras (may contain / separators).
	 */
	public static function encode( array $selected, string $custom = '' ): string {
		$allowed = array_keys( self::options() );
		$picked  = array();
		$seen    = array();

		foreach ( $allowed as $value ) {
			foreach ( $selected as $raw ) {
				$raw = sanitize_text_field( (string) $raw );
				if ( 0 === strcasecmp( $value, $raw ) ) {
					$picked[]         = $value;
					$seen[ strtolower( $value ) ] = true;
					break;
				}
			}
		}

		foreach ( self::split( $custom ) as $part ) {
			$part = sanitize_text_field( $part );
			if ( '' === $part ) {
				continue;
			}
			$key = strtolower( $part );
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			// Map custom text that matches a known option onto the canonical value.
			$mapped = false;
			foreach ( $allowed as $value ) {
				if ( 0 === strcasecmp( $value, $part ) ) {
					if ( ! isset( $seen[ strtolower( $value ) ] ) ) {
						$picked[]                     = $value;
						$seen[ strtolower( $value ) ] = true;
					}
					$mapped = true;
					break;
				}
			}
			if ( $mapped ) {
				continue;
			}
			$picked[]    = $part;
			$seen[ $key ] = true;
		}

		$out = implode( ' / ', $picked );
		if ( strlen( $out ) > self::MAX_LENGTH ) {
			$out = rtrim( substr( $out, 0, self::MAX_LENGTH ) );
		}
		return $out;
	}

	/**
	 * Sanitize from request (checkbox array and optional custom text).
	 *
	 * @param mixed  $raw   Checkbox values or legacy string.
	 * @param mixed  $other Free-text field.
	 */
	public static function from_request( $raw, $other = '' ): string {
		$custom = sanitize_text_field( (string) $other );
		if ( is_array( $raw ) ) {
			$vals = array_map(
				static function ( $v ) {
					return sanitize_text_field( (string) $v );
				},
				$raw
			);
			return self::encode( $vals, $custom );
		}
		// Legacy single string: merge with other field.
		$legacy = sanitize_text_field( (string) $raw );
		$merged = trim( $legacy . ( '' !== $custom ? ' / ' . $custom : '' ), " /\t" );
		return self::encode( self::parse( $merged ), self::custom_text( $merged ) );
	}

	/**
	 * Render checkbox group + optional custom text.
	 *
	 * @param string $name      Input name (without []).
	 * @param string $stored    Current DB value.
	 * @param string $id_prefix HTML id prefix.
	 */
	public static function render_checkboxes( string $name, string $stored = '', string $id_prefix = 'sla' ): void {
		$selected = self::parse( $stored );
		$custom   = self::custom_text( $stored );
		$other_id = $id_prefix . '-other';

		echo '<div class="itwt-sla-checks" role="group" aria-label="' . esc_attr__( 'SLA', 'it-warranty-tracker' ) . '">';
		foreach ( self::options() as $value => $label ) {
			$id      = $id_prefix . '-' . sanitize_title( $value );
			$checked = in_array( $value, $selected, true );
			// Input nested in label — do not also set for= (can double-toggle in some browsers).
			printf(
				'<label class="itwt-check"><input type="checkbox" name="%1$s[]" id="%2$s" value="%3$s" %4$s /> <span>%5$s</span></label>',
				esc_attr( $name ),
				esc_attr( $id ),
				esc_attr( $value ),
				checked( $checked, true, false ),
				esc_html( $label )
			);
		}
		printf(
			'<div class="itwt-sla-checks__other"><label class="itwt-sla-checks__other-label" for="%1$s">%2$s</label><input type="text" class="itwt-sla-checks__other-input" name="%3$s_other" id="%1$s" value="%4$s" maxlength="%5$d" placeholder="%6$s" autocomplete="off" /></div>',
			esc_attr( $other_id ),
			esc_html__( 'อื่น ๆ (กรอกเพิ่ม)', 'it-warranty-tracker' ),
			esc_attr( $name ),
			esc_attr( $custom ),
			(int) self::MAX_LENGTH,
			esc_attr__( 'เช่น 4-Hour Response', 'it-warranty-tracker' )
		);
		echo '</div>';
	}
}
