<?php
/** Analytics tab. Variables: $snapshots. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<h2 class="screen-reader-text"><?php esc_html_e( 'Database size history', 'tonkla-database-cleaner' ); ?></h2>
<?php if ( $snapshots ) : ?>
<table class="widefat striped tonkla-db-table"><thead><tr><th><?php esc_html_e( 'Date', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Total size', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Tables', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Options', 'tonkla-database-cleaner' ); ?></th></tr></thead><tbody>
	<?php foreach ( $snapshots as $s ) : ?><tr><td><?php echo esc_html( $s->snapshot_date ); ?></td><td><?php echo esc_html( size_format( (int) $s->total_bytes ) ); ?></td><td><?php echo esc_html( number_format_i18n( $s->tables_count ) ); ?></td><td><?php echo esc_html( number_format_i18n( $s->options_count ) ); ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php else : ?><p><?php esc_html_e( 'No snapshots yet. A snapshot is taken daily.', 'tonkla-database-cleaner' ); ?></p><?php endif; ?>
