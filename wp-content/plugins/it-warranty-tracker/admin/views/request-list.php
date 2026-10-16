<?php
/**
 * Support request list view.
 *
 * @package ITWT
 * @var \ITWT\Admin\Request_List_Table $table
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap itwt-wrap">
	<h1><?php esc_html_e( 'คำขอแจ้งซ่อม / ต่อ MA', 'it-warranty-tracker' ); ?></h1>
	<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'อัปเดตสถานะแล้ว', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>
	<form method="get">
		<input type="hidden" name="page" value="itwt-requests" />
		<?php $table->display(); ?>
	</form>
</div>
