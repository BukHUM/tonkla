<?php
/**
 * Shared admin menu slugs and URL helpers for TONKLA - IT.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/** Top-level Theme Options parent (Carbon Dashboard) — keep stable for menu_order option. */
const TONKLA_ADMIN_SLUG_PARENT = 'crb_carbon_fields_container_dashboard.php';

/** Site settings (Header / Footer / Social / TOC). */
const TONKLA_ADMIN_SLUG_SITE = 'tonkla-site-settings';

/** Marketing & privacy (PDPA / Ads / Chat / Scripts). */
const TONKLA_ADMIN_SLUG_MARKETING = 'tonkla-marketing';

/** Native system tools (import + admin menu). */
const TONKLA_ADMIN_SLUG_TOOLS = 'tonkla-tools';

/** Newsletter CPT list under parent. */
const TONKLA_ADMIN_SLUG_NEWSLETTER = 'edit.php?post_type=newsletter';

/**
 * Admin URL for a Tonkla theme options / tools page.
 *
 * @param string               $slug Page slug constant value.
 * @param array<string,string> $args Extra query args (e.g. tab).
 * @return string
 */
function tonkla_admin_page_url( string $slug, array $args = array() ): string {
	if ( TONKLA_ADMIN_SLUG_NEWSLETTER === $slug ) {
		$url = admin_url( $slug );
	} elseif ( TONKLA_ADMIN_SLUG_PARENT === $slug ) {
		$url = admin_url( 'admin.php?page=' . rawurlencode( $slug ) );
	} else {
		$url = admin_url( 'admin.php?page=' . rawurlencode( $slug ) );
	}

	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * Preferred submenu order under TONKLA - IT (slug => Thai label override).
 *
 * @return array<string,string>
 */
function tonkla_theme_submenu_order(): array {
	return array(
		TONKLA_ADMIN_SLUG_PARENT     => __( 'ภาพรวม', 'tonkla' ),
		TONKLA_ADMIN_SLUG_SITE       => __( 'ตั้งค่าเว็บไซต์', 'tonkla' ),
		TONKLA_ADMIN_SLUG_MARKETING  => __( 'การตลาดและความเป็นส่วนตัว', 'tonkla' ),
		TONKLA_ADMIN_SLUG_NEWSLETTER => __( 'สมาชิกจดหมายข่าว', 'tonkla' ),
		TONKLA_ADMIN_SLUG_TOOLS      => __( 'เครื่องมือระบบ', 'tonkla' ),
	);
}

/**
 * Reorder and relabel TONKLA - IT submenu from a single source of truth.
 */
function tonkla_order_theme_submenu(): void {
	global $submenu;

	$parent = TONKLA_ADMIN_SLUG_PARENT;
	if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
		return;
	}

	$existing = $submenu[ $parent ];
	$by_slug  = array();

	foreach ( $existing as $item ) {
		if ( empty( $item[2] ) ) {
			continue;
		}
		$by_slug[ (string) $item[2] ] = $item;
	}

	// WP only auto-adds the parent row when the top-level page exists before the first
	// submenu registers; Carbon registers late, so the Overview link can go missing.
	if ( ! isset( $by_slug[ $parent ] ) ) {
		$by_slug[ $parent ] = array(
			__( 'ภาพรวม', 'tonkla' ),
			'manage_options',
			$parent,
			__( 'ภาพรวม', 'tonkla' ),
		);
	}

	// Ensure newsletter CPT row exists (may be registered under parent already).
	if ( ! isset( $by_slug[ TONKLA_ADMIN_SLUG_NEWSLETTER ] ) ) {
		$by_slug[ TONKLA_ADMIN_SLUG_NEWSLETTER ] = array(
			__( 'สมาชิกจดหมายข่าว', 'tonkla' ),
			'manage_options',
			TONKLA_ADMIN_SLUG_NEWSLETTER,
			__( 'สมาชิกจดหมายข่าว', 'tonkla' ),
		);
	}

	$ordered = array();
	foreach ( tonkla_theme_submenu_order() as $slug => $label ) {
		if ( ! isset( $by_slug[ $slug ] ) ) {
			continue;
		}
		$item       = $by_slug[ $slug ];
		$item[0]    = $label;
		$item[3]    = $label;
		$ordered[]  = $item;
		unset( $by_slug[ $slug ] );
	}

	// Append any leftover items (plugins adding under this parent).
	foreach ( $by_slug as $item ) {
		$ordered[] = $item;
	}

	$submenu[ $parent ] = $ordered;
}
add_action( 'admin_menu', 'tonkla_order_theme_submenu', 9999 );

/**
 * Thai label for Carbon Fields "Save Changes" on Tonkla admin screens.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @return string
 */
function tonkla_carbon_fields_thai_strings( $translation, $text ) {
	if ( ! is_admin() ) {
		return $translation;
	}
	if ( 'Save Changes' === $text ) {
		return __( 'บันทึกการตั้งค่า', 'tonkla' );
	}
	if ( 'Actions' === $text ) {
		return __( 'การดำเนินการ', 'tonkla' );
	}
	if ( 'Settings saved.' === $text ) {
		return __( 'บันทึกการตั้งค่าแล้ว', 'tonkla' );
	}
	return $translation;
}
add_filter( 'gettext_carbon-fields', 'tonkla_carbon_fields_thai_strings', 10, 2 );
