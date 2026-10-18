<?php
/**
 * Native System Tools page (demo import + admin menu order).
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Redirect legacy Carbon Tools / Admin Menu URLs to the native System Tools page.
 */
function tonkla_redirect_legacy_tools_pages(): void {
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' === $page ) {
		return;
	}

	$site_needles = array(
		'header_footer',
		'social_media',
		'table_of_contents',
	);
	$marketing_needles = array(
		'legal_pdpa',
		'advertising',
		'customer_service',
		'custom_scripts',
	);

	foreach ( $site_needles as $needle ) {
		if ( false !== strpos( $page, $needle ) ) {
			wp_safe_redirect( tonkla_admin_page_url( TONKLA_ADMIN_SLUG_SITE ) );
			exit;
		}
	}
	foreach ( $marketing_needles as $needle ) {
		if ( false !== strpos( $page, $needle ) ) {
			wp_safe_redirect( tonkla_admin_page_url( TONKLA_ADMIN_SLUG_MARKETING ) );
			exit;
		}
	}

	$is_legacy_tools = ( false !== strpos( $page, 'container_tools' ) );
	$is_legacy_menu  = ( 'tonkla-admin-menu-order' === $page );
	if ( ! $is_legacy_tools && ! $is_legacy_menu ) {
		return;
	}
	$tab = $is_legacy_menu ? 'menu' : 'import';
	wp_safe_redirect( tonkla_admin_page_url( TONKLA_ADMIN_SLUG_TOOLS, array( 'tab' => $tab ) ) );
	exit;
}
add_action( 'admin_init', 'tonkla_redirect_legacy_tools_pages', 4 );

/**
 * Register System Tools submenu under TONKLA - IT.
 */
function tonkla_register_admin_tools_page(): void {
	add_submenu_page(
		TONKLA_ADMIN_SLUG_PARENT,
		__( 'เครื่องมือระบบ', 'tonkla' ),
		__( 'เครื่องมือระบบ', 'tonkla' ),
		'edit_theme_options',
		TONKLA_ADMIN_SLUG_TOOLS,
		'tonkla_render_admin_tools_page'
	);
}
add_action( 'admin_menu', 'tonkla_register_admin_tools_page', 20 );

/**
 * Allowed tools tabs.
 *
 * @return array<string,string>
 */
function tonkla_admin_tools_tabs(): array {
	return array(
		'import' => __( 'นำเข้าเนื้อหาตัวอย่าง', 'tonkla' ),
		'menu'   => __( 'จัดเมนูแอดมิน', 'tonkla' ),
	);
}

/**
 * Current tools tab from request.
 *
 * @return string
 */
function tonkla_admin_tools_current_tab(): string {
	$tabs = tonkla_admin_tools_tabs();
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : 'import'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $tabs[ $tab ] ) ? $tab : 'import';
}

/**
 * Enqueue assets for System Tools.
 *
 * @param string $hook_suffix Admin hook.
 */
function tonkla_enqueue_admin_tools_assets( string $hook_suffix ): void {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( TONKLA_ADMIN_SLUG_TOOLS !== $page && false === strpos( $hook_suffix, TONKLA_ADMIN_SLUG_TOOLS ) ) {
		return;
	}

	tonkla_enqueue_admin_style();

	wp_enqueue_script(
		'tonkla-admin-tools',
		get_template_directory_uri() . '/assets/js/admin-tools.js',
		array(),
		(string) filemtime( get_template_directory() . '/assets/js/admin-tools.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( 'menu' === tonkla_admin_tools_current_tab() ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'tonkla-admin-menu-order',
			get_template_directory_uri() . '/assets/js/admin-menu-order.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			(string) filemtime( get_template_directory() . '/assets/js/admin-menu-order.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_localize_script(
			'tonkla-admin-menu-order',
			'tonklaMenuOrder',
			array(
				'separatorLabel' => __( '— เส้นคั่นกลุ่ม —', 'tonkla' ),
				'removeLabel'    => __( 'ลบเส้นคั่น', 'tonkla' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'tonkla_enqueue_admin_tools_assets' );

/**
 * Render System Tools page.
 */
function tonkla_render_admin_tools_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla' ) );
	}

	$tabs       = tonkla_admin_tools_tabs();
	$active_tab = tonkla_admin_tools_current_tab();
	$base_url   = tonkla_admin_page_url( TONKLA_ADMIN_SLUG_TOOLS );

	$tonkla_tabs       = $tabs;
	$tonkla_active_tab = $active_tab;
	$tonkla_base_url   = $base_url;
	?>
	<div class="wrap tonkla-tools">
		<h1 class="tonkla-tools__title"><?php esc_html_e( 'เครื่องมือระบบ', 'tonkla' ); ?></h1>

		<div class="tonkla-tools__panel">
			<?php
			include get_template_directory() . '/parts/admin/tabs.php';

			if ( 'menu' === $active_tab ) {
				tonkla_render_admin_menu_order_panel();
			} else {
				require_once get_template_directory() . '/inc/admin-demo-import-ui.php';
				echo tonkla_render_demo_import_ui(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside renderer.
			}
			?>
		</div>
	</div>
	<?php
}
