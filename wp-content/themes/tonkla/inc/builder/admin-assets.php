<?php
/**
 * Admin assets for the Flexible Page Builder.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current admin screen is editing a Flexible Page Builder page.
 *
 * @return bool
 */
function tonkla_is_page_builder_screen() {
	if ( ! is_admin() ) {
		return false;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->base, array( 'post', 'page' ), true ) ) {
		return false;
	}
	if ( 'page' !== $screen->post_type ) {
		return false;
	}

	$post_id = 0;
	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( ! $post_id && isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
		$post_id = (int) $GLOBALS['post']->ID;
	}

	if ( $post_id ) {
		$slug = get_page_template_slug( $post_id );
		if ( 'page-flexible.php' === $slug ) {
			return true;
		}
	}

	// New page or template just switched — allow assets; JS gates on #page_template.
	return true;
}

/**
 * Whether nav-menus screen (icon_class fields).
 *
 * @return bool
 */
function tonkla_is_nav_menus_screen() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return $screen && 'nav-menus' === $screen->base;
}

/**
 * Enqueue builder admin CSS/JS and FontAwesome.
 *
 * @param string $hook_suffix Admin hook.
 */
function tonkla_enqueue_builder_admin_assets( $hook_suffix = '' ) {
	$is_builder = tonkla_is_page_builder_screen();
	$is_menus   = tonkla_is_nav_menus_screen();

	if ( ! $is_builder && ! $is_menus ) {
		// Early enqueue before screen is ready.
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'nav-menus.php' ), true ) ) {
			return;
		}
	}

	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	wp_enqueue_style(
		'tonkla-fontawesome-admin',
		$theme_uri . '/assets/vendor/fontawesome/css/all.min.css',
		array(),
		'6.4.0'
	);

	wp_enqueue_style(
		'tonkla-builder-admin',
		$theme_uri . '/assets/css/builder-admin.css',
		array( 'tonkla-fontawesome-admin' ),
		(string) filemtime( $theme_dir . '/assets/css/builder-admin.css' )
	);

	$builder_deps = array();
	// Prefer loading after Carbon Fields React metaboxes.
	foreach ( array( 'carbon-fields-metaboxes', 'carbon-fields-core', 'carbon-fields-vendor' ) as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
			$builder_deps[] = $handle;
			break;
		}
	}

	wp_enqueue_script(
		'tonkla-builder-admin',
		$theme_uri . '/assets/js/builder-admin.js',
		$builder_deps,
		(string) filemtime( $theme_dir . '/assets/js/builder-admin.js' ),
		array(
			'in_footer' => true,
		)
	);

	$modules = array();
	foreach ( tonkla_builder_modules() as $slug => $meta ) {
		$modules[] = array(
			'slug'        => $slug,
			'label'       => $meta['label'],
			'description' => $meta['description'],
			'wireframe'   => $meta['wireframe'],
		);
	}

	wp_localize_script(
		'tonkla-builder-admin',
		'tonklaBuilder',
		array(
			'iconsUrl'   => $theme_uri . '/assets/data/fa-icons.json',
			'modules'    => $modules,
			'colorMap'   => tonkla_icon_color_hex_map(),
			'i18n'       => array(
				'browse'       => __( 'เลือกไอคอน', 'tonkla' ),
				'clear'        => __( 'ล้าง', 'tonkla' ),
				'searchIcons'  => __( 'ค้นหาไอคอน…', 'tonkla' ),
				'allStyles'    => __( 'ทั้งหมด', 'tonkla' ),
				'solid'        => __( 'Solid', 'tonkla' ),
				'regular'      => __( 'Regular', 'tonkla' ),
				'brands'       => __( 'Brands', 'tonkla' ),
				'noResults'    => __( 'ไม่พบไอคอน', 'tonkla' ),
				'loading'      => __( 'กำลังโหลด…', 'tonkla' ),
				'pickModule'   => __( 'เลือกโมดูล', 'tonkla' ),
				'searchModule' => __( 'ค้นหาโมดูล…', 'tonkla' ),
				'close'        => __( 'ปิด', 'tonkla' ),
				'emptyModules' => __( 'ยังไม่มีโมดูล — กดปุ่ม + เพื่อเพิ่ม', 'tonkla' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'tonkla_enqueue_builder_admin_assets', 20 );

