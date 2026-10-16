<?php
/**
 * Admin dashboard view.
 *
 * @package ITWT
 * @var array<string,int> $counts
 * @var array<int,object> $expiring30
 * @var array<int,object> $expiring60
 * @var int               $new_reqs
 * @var array             $quota
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap itwt-wrap">
	<h1><?php esc_html_e( 'IT Warranty — แดชบอร์ด', 'it-warranty-tracker' ); ?></h1>

	<div class="itwt-dash-grid">
		<div class="itwt-stat">
			<span class="itwt-stat__label"><?php esc_html_e( 'อยู่ในประกัน', 'it-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) ( $counts['covered'] ?? 0 ) ); ?></span>
		</div>
		<div class="itwt-stat">
			<span class="itwt-stat__label"><?php esc_html_e( 'ใกล้หมด', 'it-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) ( $counts['expiring'] ?? 0 ) ); ?></span>
		</div>
		<div class="itwt-stat">
			<span class="itwt-stat__label"><?php esc_html_e( 'หมดประกัน', 'it-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) ( $counts['expired'] ?? 0 ) ); ?></span>
		</div>
		<div class="itwt-stat">
			<span class="itwt-stat__label"><?php esc_html_e( 'คำขอใหม่', 'it-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><a href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-requests&status=new' ) ); ?>"><?php echo esc_html( (string) $new_reqs ); ?></a></span>
		</div>
		<div class="itwt-stat">
			<span class="itwt-stat__label"><?php esc_html_e( 'โควตา LINE', 'it-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) ( $quota['used'] ?? 0 ) . '/' . (string) ( $quota['quota'] ?? 300 ) ); ?></span>
		</div>
	</div>

	<h2><?php esc_html_e( 'ใกล้หมดภายใน 30 วัน', 'it-warranty-tracker' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Service Tag', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'รุ่น', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'หมดวันที่', 'it-warranty-tracker' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $expiring30 ) ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'ไม่มีรายการ', 'it-warranty-tracker' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $expiring30 as $row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $row->id ) ); ?>"><?php echo esc_html( (string) $row->service_tag ); ?></a></td>
						<td><?php echo esc_html( (string) $row->model_name ); ?></td>
						<td><?php echo esc_html( (string) ( $row->max_end ?? '' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'ใกล้หมดภายใน 60 วัน', 'it-warranty-tracker' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Service Tag', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'รุ่น', 'it-warranty-tracker' ); ?></th>
				<th><?php esc_html_e( 'หมดวันที่', 'it-warranty-tracker' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $expiring60 ) ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'ไม่มีรายการ', 'it-warranty-tracker' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $expiring60 as $row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $row->id ) ); ?>"><?php echo esc_html( (string) $row->service_tag ); ?></a></td>
						<td><?php echo esc_html( (string) $row->model_name ); ?></td>
						<td><?php echo esc_html( (string) ( $row->max_end ?? '' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
