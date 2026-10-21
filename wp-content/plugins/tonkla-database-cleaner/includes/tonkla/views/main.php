<?php
/** Tonkla Database Cleaner layout: sticky safety bar, tabs. Variables: $tab, $tabs, plus per-tab data. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$base = admin_url( 'admin.php?page=tonkla-database-cleaner' );
?>
<div class="wrap tonkla-db-wrap">
	<h1><?php esc_html_e( 'Tonkla Database Cleaner', 'tonkla-database-cleaner' ); ?></h1>

	<div class="tonkla-db-safety">
		<strong><?php esc_html_e( 'Warning:', 'tonkla-database-cleaner' ); ?></strong>
		<?php esc_html_e( 'Deleting database items is permanent. Review every item before deleting.', 'tonkla-database-cleaner' ); ?>
		<label class="tonkla-db-backup"><input type="checkbox" id="tonkla-db-backup"> <?php esc_html_e( 'I have backed up the database', 'tonkla-database-cleaner' ); ?></label>
	</div>

	<nav class="nav-tab-wrapper tonkla-db-tabs" aria-label="<?php esc_attr_e( 'Database cleaner sections', 'tonkla-database-cleaner' ); ?>">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a href="<?php echo esc_url( $base . '&tab=' . $key ); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<div class="tonkla-db-tabpanel">
		<?php include __DIR__ . '/tab-' . $tab . '.php'; ?>
	</div>
</div>
