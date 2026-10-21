<?php
/** Overview tab. Variables: $summary, $logs. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$base = admin_url( 'admin.php?page=tonkla-database-cleaner' );
?>
<div class="tonkla-db-cards">
	<a class="tonkla-db-card" href="<?php echo esc_url( $base . '&tab=general' ); ?>">
		<span class="tonkla-db-card-num"><?php echo esc_html( number_format_i18n( $summary['general_total'] ) ); ?></span>
		<span class="tonkla-db-card-label"><?php esc_html_e( 'General cleanup items', 'tonkla-database-cleaner' ); ?></span>
	</a>
	<a class="tonkla-db-card" href="<?php echo esc_url( $base . '&tab=orphan' ); ?>">
		<span class="tonkla-db-card-num tonkla-db-num-warn"><?php echo esc_html( number_format_i18n( $summary['orphan_deletable'] ) ); ?></span>
		<span class="tonkla-db-card-label"><?php esc_html_e( 'Orphan candidates (deletable)', 'tonkla-database-cleaner' ); ?></span>
	</a>
	<a class="tonkla-db-card" href="<?php echo esc_url( $base . '&tab=analytics' ); ?>">
		<span class="tonkla-db-card-num"><?php echo esc_html( $summary['db_size'] ? size_format( $summary['db_size'] ) : '—' ); ?></span>
		<span class="tonkla-db-card-label"><?php esc_html_e( 'Database size (latest snapshot)', 'tonkla-database-cleaner' ); ?></span>
	</a>
	<a class="tonkla-db-card" href="<?php echo esc_url( $base . '&tab=logs' ); ?>">
		<span class="tonkla-db-card-num"><?php echo esc_html( $summary['last_snapshot'] ? $summary['last_snapshot'] : '—' ); ?></span>
		<span class="tonkla-db-card-label"><?php esc_html_e( 'Last snapshot date', 'tonkla-database-cleaner' ); ?></span>
	</a>
</div>

<div class="tonkla-db-dangerzone">
	<div>
		<strong><?php esc_html_e( 'Clear everything that is not needed', 'tonkla-database-cleaner' ); ?></strong>
		<p><?php esc_html_e( 'Deletes every orphan candidate and expired item found by the latest scans: options, transients and cron jobs. Tables and post/user meta are never touched by this button - delete those from their Orphan Scan types after review. WordPress core and active plugin/theme items stay locked, and every item is re-checked right before it is deleted. Run scans first if the numbers look out of date.', 'tonkla-database-cleaner' ); ?></p>
	</div>
	<button type="button" class="button tonkla-db-danger" id="tonkla-db-delete-all"><?php esc_html_e( 'Delete ALL deletable items', 'tonkla-database-cleaner' ); ?> (<?php echo esc_html( number_format_i18n( $summary['orphan_deletable'] ) ); ?>)</button>
</div>

<h2><?php esc_html_e( 'Recent cleanup activity', 'tonkla-database-cleaner' ); ?></h2>
<?php if ( $logs ) : ?>
<table class="widefat striped tonkla-db-table"><thead><tr><th><?php esc_html_e( 'When', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Action', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Type', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Item', 'tonkla-database-cleaner' ); ?></th></tr></thead><tbody>
	<?php foreach ( $logs as $log ) : ?><tr><td><?php echo esc_html( $log->created_at ); ?></td><td><?php echo esc_html( $log->action_type ); ?></td><td><?php echo esc_html( $log->item_type ); ?></td><td><?php echo esc_html( $log->item_name ); ?></td></tr><?php endforeach; ?>
</tbody></table>
<p><a href="<?php echo esc_url( $base . '&tab=logs' ); ?>"><?php esc_html_e( 'View full cleanup log', 'tonkla-database-cleaner' ); ?></a></p>
<?php else : ?><p><?php esc_html_e( 'No cleanup actions logged yet.', 'tonkla-database-cleaner' ); ?></p><?php endif; ?>
