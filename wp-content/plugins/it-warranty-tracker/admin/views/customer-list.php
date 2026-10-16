<?php
/**
 * Customer list / form view.
 *
 * @package ITWT
 * @var array       $result
 * @var object|null $customer
 * @var string      $search
 * @var int         $page
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap itwt-wrap">
	<h1><?php esc_html_e( 'ลูกค้า', 'it-warranty-tracker' ); ?></h1>

	<?php if ( ! empty( $_GET['updated'] ) || ! empty( $_GET['deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'ดำเนินการสำเร็จ', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>
	<?php if ( ! empty( $_GET['error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error"><p><?php esc_html_e( 'ลบไม่ได้เพราะยังมีทรัพย์สินผูกอยู่', 'it-warranty-tracker' ); ?></p></div>
	<?php endif; ?>

	<form method="post" class="itwt-customer-form">
		<?php wp_nonce_field( 'itwt_save_customer' ); ?>
		<input type="hidden" name="itwt_action" value="save_customer" />
		<input type="hidden" name="customer_id" value="<?php echo esc_attr( (string) (int) ( $customer->id ?? 0 ) ); ?>" />
		<h2><?php echo $customer ? esc_html__( 'แก้ไขลูกค้า', 'it-warranty-tracker' ) : esc_html__( 'เพิ่มลูกค้า', 'it-warranty-tracker' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="customer_code"><?php esc_html_e( 'รหัสลูกค้า', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="customer_code" name="customer_code" class="regular-text" value="<?php echo esc_attr( (string) ( $customer->customer_code ?? '' ) ); ?>" required /></td>
			</tr>
			<tr>
				<th><label for="company_name"><?php esc_html_e( 'ชื่อบริษัท', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="company_name" name="company_name" class="regular-text" value="<?php echo esc_attr( (string) ( $customer->company_name ?? '' ) ); ?>" required /></td>
			</tr>
			<tr>
				<th><label for="contact_name"><?php esc_html_e( 'ผู้ติดต่อ', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="contact_name" name="contact_name" class="regular-text" value="<?php echo esc_attr( (string) ( $customer->contact_name ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="phone"><?php esc_html_e( 'เบอร์', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="text" id="phone" name="phone" class="regular-text" value="<?php echo esc_attr( (string) ( $customer->phone ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="email"><?php esc_html_e( 'อีเมล', 'it-warranty-tracker' ); ?></label></th>
				<td><input type="email" id="email" name="email" class="regular-text" value="<?php echo esc_attr( (string) ( $customer->email ?? '' ) ); ?>" /></td>
			</tr>
		</table>
		<?php submit_button( __( 'บันทึกลูกค้า', 'it-warranty-tracker' ) ); ?>
	</form>

	<form method="get">
		<input type="hidden" name="page" value="itwt-customers" />
		<p class="search-box">
			<label class="screen-reader-text" for="customer-search"><?php esc_html_e( 'ค้นหา', 'it-warranty-tracker' ); ?></label>
			<input type="search" id="customer-search" name="s" value="<?php echo esc_attr( $search ); ?>" />
			<?php submit_button( __( 'ค้นหา', 'it-warranty-tracker' ), '', '', false ); ?>
		</p>
	</form>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'รหัส', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'บริษัท', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'ผู้ติดต่อ', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'เบอร์', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'จัดการ', 'it-warranty-tracker' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'ไม่มีข้อมูล', 'it-warranty-tracker' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['items'] as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $row->customer_code ); ?></td>
						<td><?php echo esc_html( (string) $row->company_name ); ?></td>
						<td><?php echo esc_html( (string) $row->contact_name ); ?></td>
						<td><?php echo esc_html( (string) $row->phone ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-customers&edit=' . (int) $row->id ) ); ?>"><?php esc_html_e( 'แก้ไข', 'it-warranty-tracker' ); ?></a>
							<form method="post" style="display:inline" onsubmit="return confirm(itwtAdmin.i18n.confirmDelete);">
								<?php wp_nonce_field( 'itwt_delete_customer' ); ?>
								<input type="hidden" name="itwt_action" value="delete_customer" />
								<input type="hidden" name="customer_id" value="<?php echo esc_attr( (string) (int) $row->id ); ?>" />
								<button type="submit" class="button-link-delete"><?php esc_html_e( 'ลบ', 'it-warranty-tracker' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
