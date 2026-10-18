<?php
/**
 * Shared admin tab navigation.
 *
 * Expected vars:
 * - $tonkla_tabs       array<string,string> slug => label
 * - $tonkla_active_tab string
 * - $tonkla_base_url   string base admin URL without tab
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $tonkla_tabs ) || ! is_array( $tonkla_tabs ) ) {
	return;
}

$tonkla_active_tab = isset( $tonkla_active_tab ) ? (string) $tonkla_active_tab : '';
$tonkla_base_url   = isset( $tonkla_base_url ) ? (string) $tonkla_base_url : '';
?>
<nav class="tonkla-admin-tabs" aria-label="<?php esc_attr_e( 'แท็บเครื่องมือ', 'tonkla' ); ?>">
	<ul class="tonkla-admin-tabs__list">
		<?php foreach ( $tonkla_tabs as $tab_slug => $tab_label ) : ?>
			<?php
			$is_active = ( $tonkla_active_tab === $tab_slug );
			$tab_url   = add_query_arg( 'tab', $tab_slug, $tonkla_base_url );
			?>
			<li class="tonkla-admin-tabs__item">
				<a
					class="tonkla-admin-tabs__link<?php echo $is_active ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $tab_url ); ?>"
					<?php echo $is_active ? ' aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $tab_label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
