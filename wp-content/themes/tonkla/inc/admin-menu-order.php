<?php
/**
 * Custom wp-admin menu grouping / order (editable in TONKLA - IT).
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option key (autoload = false).
 */
const TONKLA_ADMIN_MENU_ORDER_OPTION = 'tonkla_admin_menu_order';

/**
 * Whether a menu slug is a group separator.
 *
 * @param string $slug Menu file / slug.
 */
function tonkla_is_admin_menu_separator( string $slug ): bool {
	return 0 === strpos( $slug, 'separator' );
}

/**
 * Sanitize a menu order slug (pages or separators).
 *
 * @param string $slug Raw slug.
 */
function tonkla_sanitize_admin_menu_slug( string $slug ): string {
	$slug = sanitize_text_field( $slug );
	if ( '' === $slug ) {
		return '';
	}
	if ( tonkla_is_admin_menu_separator( $slug ) ) {
		$slug = preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $slug ) ) ?? '';
		if ( ! tonkla_is_admin_menu_separator( $slug ) || strlen( $slug ) > 64 ) {
			return '';
		}
		return $slug;
	}
	return $slug;
}

/**
 * Default B2B menu order for Tonkla.
 *
 * @return array<int,string>
 */
function tonkla_default_admin_menu_order(): array {
	return array(
		'index.php',
		'crb_carbon_fields_container_dashboard.php',
		'itwt-dashboard',
		'separator1',
		'edit.php',
		'upload.php',
		'edit.php?post_type=page',
		'edit-comments.php',
		'wpcf7',
		'separator2',
		'themes.php',
		'plugins.php',
		'users.php',
		'tools.php',
		'options-general.php',
		'separator-last',
	);
}

/**
 * Saved order or defaults.
 *
 * @return array<int,string>
 */
function tonkla_get_admin_menu_order(): array {
	$stored = get_option( TONKLA_ADMIN_MENU_ORDER_OPTION, null );
	if ( ! is_array( $stored ) || empty( $stored ) ) {
		return tonkla_default_admin_menu_order();
	}

	$clean = array();
	$seen  = array();
	foreach ( $stored as $slug ) {
		$slug = is_string( $slug ) ? tonkla_sanitize_admin_menu_slug( $slug ) : '';
		if ( '' === $slug || isset( $seen[ $slug ] ) ) {
			continue;
		}
		$seen[ $slug ] = true;
		$clean[]       = $slug;
	}

	return ! empty( $clean ) ? $clean : tonkla_default_admin_menu_order();
}

/**
 * Enable custom menu order.
 *
 * @return bool
 */
function tonkla_enable_custom_menu_order(): bool {
	return true;
}
add_filter( 'custom_menu_order', 'tonkla_enable_custom_menu_order' );

/**
 * Apply saved / default order; append unknown non-separator items at the end.
 *
 * @param array<int,string> $menu_order Current order of menu slugs.
 * @return array<int,string>
 */
function tonkla_filter_admin_menu_order( array $menu_order ): array {
	$preferred = tonkla_get_admin_menu_order();
	$ordered   = array();

	foreach ( $preferred as $slug ) {
		if ( tonkla_is_admin_menu_separator( $slug ) || in_array( $slug, $menu_order, true ) ) {
			if ( ! in_array( $slug, $ordered, true ) ) {
				$ordered[] = $slug;
			}
		}
	}

	foreach ( $menu_order as $slug ) {
		if ( in_array( $slug, $ordered, true ) ) {
			continue;
		}
		// Skip separators not chosen in the saved order.
		if ( tonkla_is_admin_menu_separator( (string) $slug ) ) {
			continue;
		}
		$ordered[] = $slug;
	}

	return $ordered;
}
add_filter( 'menu_order', 'tonkla_filter_admin_menu_order' );

/**
 * Ensure preferred separators exist in $menu; drop separators not in the order.
 */
function tonkla_sync_admin_menu_separators(): void {
	global $menu;

	if ( ! is_array( $menu ) ) {
		return;
	}

	$preferred = tonkla_get_admin_menu_order();
	$wanted    = array();
	foreach ( $preferred as $slug ) {
		if ( tonkla_is_admin_menu_separator( $slug ) ) {
			$wanted[ $slug ] = true;
		}
	}

	$existing = array();
	foreach ( $menu as $key => $item ) {
		if ( ! is_array( $item ) || empty( $item[2] ) ) {
			continue;
		}
		$slug = (string) $item[2];
		if ( ! tonkla_is_admin_menu_separator( $slug ) ) {
			continue;
		}
		if ( ! isset( $wanted[ $slug ] ) ) {
			unset( $menu[ $key ] );
			continue;
		}
		$existing[ $slug ] = true;
	}

	foreach ( array_keys( $wanted ) as $slug ) {
		if ( isset( $existing[ $slug ] ) ) {
			continue;
		}
		$menu[] = array( '', 'read', $slug, '', 'wp-menu-separator' );
	}
}
add_action( 'admin_menu', 'tonkla_sync_admin_menu_separators', 9999 );

/**
 * Snapshot of top-level admin menus (slug => label).
 *
 * @return array<string,string>
 */
function tonkla_get_toplevel_admin_menus(): array {
	global $menu;

	$items = array();
	if ( ! is_array( $menu ) ) {
		return $items;
	}

	foreach ( $menu as $item ) {
		if ( ! is_array( $item ) || empty( $item[2] ) ) {
			continue;
		}
		$slug  = (string) $item[2];
		$label = wp_strip_all_tags( (string) ( $item[0] ?? $slug ) );
		$label = trim( preg_replace( '/\d+$/', '', $label ) ?? $label );
		if ( '' === $label ) {
			$label = $slug;
		}
		if ( tonkla_is_admin_menu_separator( $slug ) ) {
			$label = __( '— เส้นคั่นกลุ่ม —', 'tonkla' );
		}
		$items[ $slug ] = $label;
	}

	return $items;
}

/**
 * Build display list: preferred order first, then leftover menus (not separators).
 *
 * @return array<int,array{slug:string,label:string}>
 */
function tonkla_build_admin_menu_editor_rows(): array {
	$available = tonkla_get_toplevel_admin_menus();
	$order     = tonkla_get_admin_menu_order();
	$rows      = array();
	$seen      = array();

	foreach ( $order as $slug ) {
		if ( isset( $available[ $slug ] ) ) {
			$rows[] = array(
				'slug'  => $slug,
				'label' => $available[ $slug ],
			);
			$seen[ $slug ] = true;
			continue;
		}
		if ( tonkla_is_admin_menu_separator( $slug ) ) {
			$rows[] = array(
				'slug'  => $slug,
				'label' => __( '— เส้นคั่นกลุ่ม —', 'tonkla' ),
			);
			$seen[ $slug ] = true;
		}
	}

	foreach ( $available as $slug => $label ) {
		if ( isset( $seen[ $slug ] ) || tonkla_is_admin_menu_separator( $slug ) ) {
			continue;
		}
		$rows[] = array(
			'slug'  => $slug,
			'label' => $label,
		);
	}

	return $rows;
}

/**
 * Handle save / reset.
 */
function tonkla_handle_admin_menu_order_save(): void {
	if ( ! isset( $_POST['tonkla_admin_menu_order_action'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla' ) );
	}
	check_admin_referer( 'tonkla_admin_menu_order' );

	$action = sanitize_key( wp_unslash( (string) $_POST['tonkla_admin_menu_order_action'] ) );

	$redirect_base = function_exists( 'tonkla_admin_page_url' )
		? tonkla_admin_page_url( TONKLA_ADMIN_SLUG_TOOLS, array( 'tab' => 'menu' ) )
		: admin_url( 'admin.php?page=tonkla-tools&tab=menu' );

	if ( 'reset' === $action ) {
		delete_option( TONKLA_ADMIN_MENU_ORDER_OPTION );
		wp_safe_redirect( add_query_arg( 'tonkla_menu_reset', '1', $redirect_base ) );
		exit;
	}

	$raw = isset( $_POST['tonkla_menu_order'] ) ? wp_unslash( $_POST['tonkla_menu_order'] ) : array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	$order = array();
	$seen  = array();
	foreach ( $raw as $slug ) {
		$slug = tonkla_sanitize_admin_menu_slug( (string) $slug );
		if ( '' === $slug || isset( $seen[ $slug ] ) ) {
			continue;
		}
		$seen[ $slug ] = true;
		$order[]       = $slug;
	}

	update_option( TONKLA_ADMIN_MENU_ORDER_OPTION, $order, false );
	wp_safe_redirect( add_query_arg( 'tonkla_menu_saved', '1', $redirect_base ) );
	exit;
}
add_action( 'admin_init', 'tonkla_handle_admin_menu_order_save' );

/**
 * Render menu-order panel (used inside System Tools tab).
 */
function tonkla_render_admin_menu_order_panel(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'ไม่มีสิทธิ์', 'tonkla' ) );
	}

	$rows = tonkla_build_admin_menu_editor_rows();
	?>
	<div class="tonkla-menu-order">
		<p class="description">
			<?php esc_html_e( 'ลากรายการเพื่อจัดลำดับ กด “เพิ่มเส้นคั่นกลุ่ม” เพื่อแบ่งกลุ่มเมนู แล้วบันทึก — รายการเมนูที่ยังไม่อยู่ในลำดับจะไปอยู่ท้ายอัตโนมัติ', 'tonkla' ); ?>
		</p>

		<?php if ( ! empty( $_GET['tonkla_menu_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'บันทึกลำดับเมนูแล้ว', 'tonkla' ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['tonkla_menu_reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'คืนค่าลำดับเริ่มต้นแล้ว', 'tonkla' ); ?></p></div>
		<?php endif; ?>

		<form method="post" id="tonkla-menu-order-form">
			<?php wp_nonce_field( 'tonkla_admin_menu_order' ); ?>
			<input type="hidden" name="tonkla_admin_menu_order_action" id="tonkla-menu-order-action" value="save" />

			<p class="tonkla-menu-order__toolbar">
				<button type="button" class="button" id="tonkla-menu-add-separator">
					<?php esc_html_e( 'เพิ่มเส้นคั่นกลุ่ม', 'tonkla' ); ?>
				</button>
			</p>

			<ul id="tonkla-menu-sortable" class="tonkla-menu-sortable">
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$is_sep = tonkla_is_admin_menu_separator( $row['slug'] );
					?>
					<li class="tonkla-menu-sortable__item<?php echo $is_sep ? ' is-separator' : ''; ?>" data-slug="<?php echo esc_attr( $row['slug'] ); ?>">
						<span class="tonkla-menu-sortable__handle" aria-hidden="true">⋮⋮</span>
						<span class="tonkla-menu-sortable__label"><?php echo esc_html( $row['label'] ); ?></span>
						<code class="tonkla-menu-sortable__slug"><?php echo esc_html( $row['slug'] ); ?></code>
						<?php if ( $is_sep ) : ?>
							<button type="button" class="button-link tonkla-menu-sortable__remove" aria-label="<?php esc_attr_e( 'ลบเส้นคั่น', 'tonkla' ); ?>">&times;</button>
						<?php endif; ?>
						<input type="hidden" name="tonkla_menu_order[]" value="<?php echo esc_attr( $row['slug'] ); ?>" />
					</li>
				<?php endforeach; ?>
			</ul>

			<p class="tonkla-menu-order__actions">
				<?php submit_button( __( 'บันทึกลำดับเมนู', 'tonkla' ), 'primary', 'submit', false ); ?>
				<button type="submit" class="button" name="tonkla_admin_menu_order_action" value="reset" id="tonkla-menu-order-reset">
					<?php esc_html_e( 'คืนค่าเริ่มต้น', 'tonkla' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}
