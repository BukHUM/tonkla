<?php
/**
 * Registers the dedicated Warranty page template.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Adds "Warranty" to Page Attributes and loads the plugin layout.
 */
final class Page_Template {

	public const SLUG = 'itwt-warranty.php';

	/**
	 * Hook filters.
	 */
	public function register(): void {
		add_filter( 'theme_page_templates', array( $this, 'add_template' ) );
		add_filter( 'template_include', array( $this, 'load_template' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Add template option in the page editor.
	 *
	 * @param array<string,string> $templates Theme templates.
	 * @return array<string,string>
	 */
	public function add_template( array $templates ): array {
		$templates[ self::SLUG ] = __( 'Warranty (IT Warranty Tracker)', 'tonkla-warranty-tracker' );
		return $templates;
	}

	/**
	 * Swap in the plugin template when selected.
	 *
	 * @param string $template Path from theme.
	 */
	public function load_template( string $template ): string {
		if ( ! self::is_warranty_page() ) {
			return $template;
		}

		$path = ITWT_PLUGIN_DIR . 'public/views/page-warranty.php';
		return is_readable( $path ) ? $path : $template;
	}

	/**
	 * Body class for dedicated layout CSS.
	 *
	 * @param array<int,string> $classes Body classes.
	 * @return array<int,string>
	 */
	public function body_class( array $classes ): array {
		if ( ! self::is_warranty_page() ) {
			return $classes;
		}
		$classes[] = 'itwt-warranty-page';
		$theme     = sanitize_key( (string) ( Settings::get()['portal_theme'] ?? 'soft' ) );
		if ( ! in_array( $theme, array( 'soft', 'minimal', 'dense' ), true ) ) {
			$theme = 'soft';
		}
		$classes[] = 'itwt-theme-' . $theme;
		return $classes;
	}

	/**
	 * Whether the current (or given) page uses the Warranty template.
	 *
	 * @param int|null $post_id Optional post ID.
	 */
	public static function is_warranty_page( ?int $post_id = null ): bool {
		if ( null === $post_id ) {
			if ( ! is_singular( 'page' ) ) {
				return false;
			}
			$post_id = (int) get_queried_object_id();
		}
		if ( $post_id <= 0 ) {
			return false;
		}
		return self::SLUG === (string) get_page_template_slug( $post_id );
	}
}
