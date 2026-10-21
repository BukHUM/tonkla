<?php
/** General cleanup tab. Variables: $counts. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$groups = array(
	__( 'Content', 'tonkla-database-cleaner' ) => array(
		'revisions' => __( 'Post revisions', 'tonkla-database-cleaner' ),
		'auto_drafts' => __( 'Auto drafts', 'tonkla-database-cleaner' ),
		'trashed_posts' => __( 'Trashed posts', 'tonkla-database-cleaner' ),
	),
	__( 'Comments', 'tonkla-database-cleaner' ) => array(
		'spam_comments' => __( 'Spam comments', 'tonkla-database-cleaner' ),
		'trashed_comments' => __( 'Trashed comments', 'tonkla-database-cleaner' ),
	),
	__( 'System', 'tonkla-database-cleaner' ) => array(
		'expired_transients' => __( 'Expired transients', 'tonkla-database-cleaner' ),
	),
);
?>
<p class="tonkla-db-scanbar">
	<button type="button" class="button button-primary" id="tonkla-db-clean-selected"><?php esc_html_e( 'Clean selected', 'tonkla-database-cleaner' ); ?></button>
	<span id="tonkla-db-general-status" role="status"></span>
</p>
<table class="widefat striped tonkla-db-table tonkla-db-table-checkable tonkla-db-table-general">
	<thead><tr><th class="check-column"><input type="checkbox" id="tonkla-db-general-checkall" aria-label="<?php esc_attr_e( 'Select all cleanup items', 'tonkla-database-cleaner' ); ?>"></th><th><?php esc_html_e( 'Item', 'tonkla-database-cleaner' ); ?></th><th class="tonkla-db-col-num"><?php esc_html_e( 'Count', 'tonkla-database-cleaner' ); ?></th><th class="tonkla-db-col-action"></th></tr></thead>
	<tbody>
	<?php foreach ( $groups as $group_label => $items ) : ?>
		<tr class="tonkla-db-group-row"><td><input type="checkbox" class="tonkla-db-group-check" data-group="<?php echo esc_attr( $group_label ); ?>" aria-label="<?php echo esc_attr( $group_label ); ?>"></td><td colspan="3"><strong><?php echo esc_html( $group_label ); ?></strong></td></tr>
		<?php foreach ( $items as $key => $label ) : ?>
		<tr>
			<td><input type="checkbox" class="tonkla-db-general-check" data-group="<?php echo esc_attr( $group_label ); ?>" value="<?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"></td>
			<td><?php echo esc_html( $label ); ?></td>
			<td data-count="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( number_format_i18n( $counts[ $key ] ?? 0 ) ); ?></td>
			<td><button type="button" class="button tonkla-db-general" data-action="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Clean', 'tonkla-database-cleaner' ); ?></button></td>
		</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
		<tr class="tonkla-db-group-row"><td><input type="checkbox" class="tonkla-db-group-check" data-group="optimize" aria-label="optimize"></td><td colspan="3"><strong><?php esc_html_e( 'Maintenance', 'tonkla-database-cleaner' ); ?></strong></td></tr>
		<tr>
			<td><input type="checkbox" class="tonkla-db-general-check" data-group="optimize" value="optimize" aria-label="<?php esc_attr_e( 'Optimize all tables', 'tonkla-database-cleaner' ); ?>"></td>
			<td><?php esc_html_e( 'Optimize all tables', 'tonkla-database-cleaner' ); ?></td><td></td>
			<td><button type="button" class="button tonkla-db-general" data-action="optimize"><?php esc_html_e( 'Optimize', 'tonkla-database-cleaner' ); ?></button></td>
		</tr>
	</tbody>
</table>
