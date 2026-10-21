<?php
/** Logs tab. Variables: $logs. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<h2 class="screen-reader-text"><?php esc_html_e( 'Cleanup log', 'tonkla-database-cleaner' ); ?></h2>
<?php if ( $logs ) : ?>
<table class="widefat striped tonkla-db-table"><thead><tr><th><?php esc_html_e( 'When', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Action', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Type', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'Item', 'tonkla-database-cleaner' ); ?></th><th><?php esc_html_e( 'User', 'tonkla-database-cleaner' ); ?></th></tr></thead><tbody>
	<?php foreach ( $logs as $log ) : ?><tr><td><?php echo esc_html( $log->created_at ); ?></td><td><?php echo esc_html( $log->action_type ); ?></td><td><?php echo esc_html( $log->item_type ); ?></td><td><?php echo esc_html( $log->item_name ); ?></td><td><?php echo esc_html( (string) $log->user_id ); ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php else : ?><p><?php esc_html_e( 'No cleanup actions logged yet.', 'tonkla-database-cleaner' ); ?></p><?php endif; ?>
