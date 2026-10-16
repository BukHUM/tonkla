<?php
/**
 * Shared list footer: per-page selector + count + pagination.
 *
 * @package ITWT
 * @var string               $list_page    admin.php?page=… slug value (e.g. itwt-assets).
 * @var int                  $total        Total rows.
 * @var int                  $per_page     Current page size.
 * @var int                  $page         Current page (1-based).
 * @var array<string,string> $query_args   Extra GET args to preserve (search, filters).
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Admin_List;

$total     = max( 0, (int) ( $total ?? 0 ) );
$per_page  = Admin_List::normalize_per_page( (int) ( $per_page ?? 20 ) );
$page      = max( 1, (int) ( $page ?? 1 ) );
$pages     = max( 1, (int) ceil( $total / max( 1, $per_page ) ) );
$list_page = isset( $list_page ) ? sanitize_key( (string) $list_page ) : '';
$query_args = ( isset( $query_args ) && is_array( $query_args ) ) ? $query_args : array();

if ( $total <= 0 || '' === $list_page ) {
	return;
}

$base = admin_url( 'admin.php?page=' . $list_page );
foreach ( $query_args as $key => $value ) {
	if ( '' === (string) $value ) {
		continue;
	}
	$base = add_query_arg( sanitize_key( (string) $key ), $value, $base );
}
$base = add_query_arg( 'per_page', $per_page, $base );
$prev = max( 1, $page - 1 );
$next = min( $pages, $page + 1 );
?>
<div class="itwt-list-footer">
	<form method="get" class="itwt-per-page">
		<input type="hidden" name="page" value="<?php echo esc_attr( $list_page ); ?>" />
		<?php foreach ( $query_args as $key => $value ) : ?>
			<?php if ( '' === (string) $value ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<input type="hidden" name="<?php echo esc_attr( sanitize_key( (string) $key ) ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" />
		<?php endforeach; ?>
		<label for="itwt-per-page-<?php echo esc_attr( $list_page ); ?>"><?php esc_html_e( 'แสดง', 'it-warranty-tracker' ); ?></label>
		<select id="itwt-per-page-<?php echo esc_attr( $list_page ); ?>" name="per_page" onchange="this.form.submit()">
			<?php foreach ( Admin_List::per_page_options() as $size ) : ?>
				<option value="<?php echo esc_attr( (string) $size ); ?>" <?php selected( $per_page, $size ); ?>><?php echo esc_html( (string) $size ); ?></option>
			<?php endforeach; ?>
		</select>
		<span><?php esc_html_e( 'รายการต่อหน้า', 'it-warranty-tracker' ); ?></span>
	</form>
	<div class="tablenav-pages">
		<span class="displaying-num">
			<?php
			printf(
				/* translators: %s: total items */
				esc_html( _n( '%s รายการ', '%s รายการ', $total, 'it-warranty-tracker' ) ),
				esc_html( number_format_i18n( $total ) )
			);
			?>
		</span>
		<?php if ( $pages > 1 ) : ?>
			<span class="pagination-links">
				<a class="first-page button<?php echo 1 === $page ? ' disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'paged', 1, $base ) ); ?>"><span aria-hidden="true">&laquo;</span></a>
				<a class="prev-page button<?php echo 1 === $page ? ' disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'paged', $prev, $base ) ); ?>"><span aria-hidden="true">&lsaquo;</span></a>
				<span class="paging-input">
					<?php echo esc_html( (string) $page ); ?> / <?php echo esc_html( (string) $pages ); ?>
				</span>
				<a class="next-page button<?php echo $page >= $pages ? ' disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'paged', $next, $base ) ); ?>"><span aria-hidden="true">&rsaquo;</span></a>
				<a class="last-page button<?php echo $page >= $pages ? ' disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'paged', $pages, $base ) ); ?>"><span aria-hidden="true">&raquo;</span></a>
			</span>
		<?php endif; ?>
	</div>
</div>
