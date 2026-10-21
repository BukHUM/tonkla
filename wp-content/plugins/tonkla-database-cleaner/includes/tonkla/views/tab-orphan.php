<?php
/** Orphan scan tab. Variables: $scan_types, $scan_counts. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="tonkla-db-segmented" role="group" aria-label="<?php esc_attr_e( 'Scan types', 'tonkla-database-cleaner' ); ?>">
	<?php foreach ( $scan_types as $key => $label ) :
		$c = $scan_counts[ $key ] ?? array( 'total' => 0, 'deletable' => 0 ); ?>
		<button type="button" class="button tonkla-db-scan" data-type="<?php echo esc_attr( $key ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $c['total'] ) : ?><span class="tonkla-db-seg-count"><?php echo esc_html( sprintf( __( 'deletable %1$s / %2$s', 'tonkla-database-cleaner' ), number_format_i18n( $c['deletable'] ), number_format_i18n( $c['total'] ) ) ); ?></span><?php endif; ?>
		</button>
	<?php endforeach; ?>
</div>
<div class="tonkla-db-scanbar tonkla-db-actionbar">
	<label><input type="checkbox" id="tonkla-db-deletable" checked> <?php esc_html_e( 'Show deletable items only (orphan candidates and expired items)', 'tonkla-database-cleaner' ); ?></label>
	<button type="button" class="button button-primary" id="tonkla-db-delete" disabled><?php esc_html_e( 'Delete selected orphans', 'tonkla-database-cleaner' ); ?> (<span id="tonkla-db-selected-count">0</span>)</button>
	<button type="button" class="button tonkla-db-danger" id="tonkla-db-delete-all"><?php esc_html_e( 'Delete ALL deletable items', 'tonkla-database-cleaner' ); ?></button>
	<span id="tonkla-db-status" role="status"></span>
</div>
<table class="widefat striped tonkla-db-table tonkla-db-table-checkable tonkla-db-orphan-table">
	<thead><tr><th class="check-column"><input type="checkbox" id="tonkla-db-checkall"></th><th><?php esc_html_e( 'Name', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Belongs to', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Reason', 'tonkla-database-cleaner' ); ?></th><th class="tonkla-db-col-num"><?php esc_html_e( 'Size', 'tonkla-database-cleaner' ); ?></th></tr></thead>
	<tbody id="tonkla-db-rows"><tr><td colspan="5"><?php esc_html_e( 'Choose a scan type above. Deletable items are shown by default.', 'tonkla-database-cleaner' ); ?></td></tr></tbody>
</table>
<p><button type="button" class="button" id="tonkla-db-prev"><?php esc_html_e( 'Previous', 'tonkla-database-cleaner' ); ?></button>
<span id="tonkla-db-pageinfo"></span>
<button type="button" class="button" id="tonkla-db-next"><?php esc_html_e( 'Next', 'tonkla-database-cleaner' ); ?></button></p>
