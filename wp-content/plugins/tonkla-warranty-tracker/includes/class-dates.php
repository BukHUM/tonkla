<?php
/**
 * Display-date helpers for ITWT.
 *
 * - Read-only UI text: `01 Nov 2029` via format*()
 * - Form entry: `dd/mm/yyyy` via render_input() (native type=date follows OS locale)
 * - Storage / SQL / posted hidden value: ISO `Y-m-d`
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Formats dates for UI and form entry.
 */
final class Dates {

	/**
	 * Empty placeholder when no date.
	 */
	public const EMPTY = '—';

	/**
	 * Placeholder for form fields (Thai day/month/year).
	 */
	public const INPUT_PLACEHOLDER = 'วว/ดด/ปปปป';

	/**
	 * Format a date (Y-m-d or parseable string) for display.
	 *
	 * @param string|null $date Raw date.
	 */
	public static function format( ?string $date ): string {
		$dt = self::parse( $date );
		if ( ! $dt ) {
			return ( null === $date || '' === trim( (string) $date ) ) ? self::EMPTY : (string) $date;
		}
		return $dt->format( 'd M Y' );
	}

	/**
	 * Format a datetime (Y-m-d H:i:s) for display.
	 *
	 * @param string|null $datetime Raw datetime.
	 */
	public static function format_datetime( ?string $datetime ): string {
		$dt = self::parse( $datetime );
		if ( ! $dt ) {
			return ( null === $datetime || '' === trim( (string) $datetime ) ) ? self::EMPTY : (string) $datetime;
		}
		$raw = trim( (string) $datetime );
		if ( preg_match( '/\d{1,2}:\d{2}/', $raw ) ) {
			return $dt->format( 'd M Y H:i' );
		}
		return $dt->format( 'd M Y' );
	}

	/**
	 * Format a date range for display.
	 *
	 * @param string|null $start Start date.
	 * @param string|null $end   End date.
	 */
	public static function format_range( ?string $start, ?string $end ): string {
		$a = self::format( $start );
		$b = self::format( $end );
		if ( self::EMPTY === $a && self::EMPTY === $b ) {
			return self::EMPTY;
		}
		return $a . ' → ' . $b;
	}

	/**
	 * Convert ISO Y-m-d to dd/mm/yyyy for form display.
	 *
	 * @param string|null $ymd ISO date.
	 */
	public static function to_dmy( ?string $ymd ): string {
		$dt = self::parse( $ymd );
		return $dt ? $dt->format( 'd/m/Y' ) : '';
	}

	/**
	 * Normalize form input to ISO Y-m-d (accepts Y-m-d or d/m/Y).
	 *
	 * @param string $raw Posted value.
	 * @return string|null ISO date or null if empty/invalid.
	 */
	public static function parse_input( string $raw ): ?string {
		$raw = trim( $raw );
		if ( '' === $raw || str_starts_with( $raw, '0000-00-00' ) ) {
			return null;
		}

		$tz = wp_timezone();

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
			$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', $raw, $tz );
			return ( $dt && $dt->format( 'Y-m-d' ) === $raw ) ? $raw : null;
		}

		if ( preg_match( '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m ) ) {
			$day = (int) $m[1];
			$mon = (int) $m[2];
			$yr  = (int) $m[3];
			if ( ! checkdate( $mon, $day, $yr ) ) {
				return null;
			}
			return sprintf( '%04d-%02d-%02d', $yr, $mon, $day );
		}

		return null;
	}

	/**
	 * Render a dd/mm/yyyy date field (hidden ISO value for submit).
	 *
	 * @param array<string,mixed> $args {
	 *     @type string $name        Input name (required).
	 *     @type string $id          Text input id.
	 *     @type string $value       ISO Y-m-d value.
	 *     @type bool   $required    Required attribute on text field.
	 *     @type string $aria_label  Aria label when no visible label for=.
	 *     @type string $class       Extra class on wrapper.
	 * }
	 */
	public static function render_input( array $args ): void {
		$name  = (string) ( $args['name'] ?? '' );
		if ( '' === $name ) {
			return;
		}
		$id         = (string) ( $args['id'] ?? '' );
		$value      = (string) ( $args['value'] ?? '' );
		$required   = ! empty( $args['required'] );
		$aria_label = (string) ( $args['aria_label'] ?? '' );
		$class      = trim( 'itwt-date ' . (string) ( $args['class'] ?? '' ) );
		$iso        = self::parse_input( $value ) ?? '';
		$dmy        = '' !== $iso ? self::to_dmy( $iso ) : '';
		$text_id    = '' !== $id ? $id : '';

		printf( '<span class="%s" data-itwt-date>', esc_attr( $class ) );
		printf(
			'<input type="text" class="itwt-date__text"%s value="%s" placeholder="%s" inputmode="numeric" autocomplete="off" maxlength="10"%s%s />',
			'' !== $text_id ? ' id="' . esc_attr( $text_id ) . '"' : '',
			esc_attr( $dmy ),
			esc_attr( self::INPUT_PLACEHOLDER ),
			$required ? ' required' : '',
			'' !== $aria_label ? ' aria-label="' . esc_attr( $aria_label ) . '"' : ''
		);
		printf(
			'<input type="hidden" class="itwt-date__iso" name="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $iso )
		);
		echo '<button type="button" class="itwt-date__cal" aria-label="' . esc_attr__( 'เปิดปฏิทิน', 'tonkla-warranty-tracker' ) . '">';
		echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>';
		echo '</button>';
		printf(
			'<input type="date" class="itwt-date__native" value="%s" tabindex="-1" aria-hidden="true" />',
			esc_attr( $iso )
		);
		echo '</span>';
	}

	/**
	 * Parse into DateTimeImmutable in site timezone.
	 *
	 * @param string|null $value Raw value.
	 */
	private static function parse( ?string $value ): ?\DateTimeImmutable {
		if ( null === $value ) {
			return null;
		}
		$value = trim( $value );
		if ( '' === $value || str_starts_with( $value, '0000-00-00' ) ) {
			return null;
		}

		$tz = wp_timezone();

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $tz );
			return $dt instanceof \DateTimeImmutable ? $dt : null;
		}

		$from_dmy = self::parse_input( $value );
		if ( null !== $from_dmy ) {
			$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', $from_dmy, $tz );
			return $dt instanceof \DateTimeImmutable ? $dt : null;
		}

		$dt = date_create_immutable( $value, $tz );
		return $dt instanceof \DateTimeImmutable ? $dt : null;
	}
}
