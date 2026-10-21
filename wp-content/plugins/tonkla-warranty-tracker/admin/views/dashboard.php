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

$today       = wp_date( 'Y-m-d' );
$covered     = (int) ( $counts['covered'] ?? 0 );
$expiring    = (int) ( $counts['expiring'] ?? 0 );
$expired     = (int) ( $counts['expired'] ?? 0 );
$quota_used  = (int) ( $quota['used'] ?? 0 );
$quota_total = (int) ( $quota['quota'] ?? 300 );
$quota_pct   = $quota_total > 0 ? min( 100, (int) round( ( $quota_used / $quota_total ) * 100 ) ) : 0;

/**
 * Render expiring table body.
 *
 * @param array<int,object> $rows Rows.
 * @param string            $today Y-m-d.
 */
$itwt_render_expiring = static function ( array $rows, string $today ): void {
	if ( empty( $rows ) ) {
		?>
		<tr class="itwt-table__empty-row">
			<td colspan="4">
				<div class="itwt-empty">
					<svg class="itwt-empty__icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 12h2v5H7zm4-7h2v12h-2zm4 4h2v8h-2z"/></svg>
					<p class="itwt-empty__title"><?php esc_html_e( 'ไม่มีรายการใกล้หมดในช่วงนี้', 'tonkla-warranty-tracker' ); ?></p>
					<p class="itwt-empty__hint"><?php esc_html_e( 'เมื่อมีสัญญาใกล้หมดอายุ จะแสดงที่นี่', 'tonkla-warranty-tracker' ); ?></p>
				</div>
			</td>
		</tr>
		<?php
		return;
	}
	foreach ( $rows as $row ) {
		$days = max( 0, \ITWT\Coverage::days_between( $today, (string) ( $row->max_end ?? $today ) ) );
		$tone = $days <= 14 ? 'urgent' : ( $days <= 30 ? 'warn' : 'ok' );
		?>
		<tr>
			<td>
				<a class="itwt-tag-link" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&action=edit&id=' . (int) $row->id ) ); ?>">
					<?php echo esc_html( (string) $row->service_tag ); ?>
				</a>
			</td>
			<td><?php echo esc_html( (string) $row->model_name ); ?></td>
			<td><?php echo esc_html( \ITWT\Dates::format( isset( $row->max_end ) ? (string) $row->max_end : null ) ); ?></td>
			<td>
				<span class="itwt-days itwt-days--<?php echo esc_attr( $tone ); ?>">
					<?php
					printf(
						/* translators: %d: days remaining */
						esc_html__( '%d วัน', 'tonkla-warranty-tracker' ),
						(int) $days
					);
					?>
				</span>
			</td>
		</tr>
		<?php
	}
};
?>
<div class="wrap itwt-wrap itwt-dash">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'tonkla-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'แดชบอร์ด', 'tonkla-warranty-tracker' ); ?></h1>
		</div>
		<div class="itwt-dash__quota" title="<?php esc_attr_e( 'โควตาข้อความ LINE เดือนนี้', 'tonkla-warranty-tracker' ); ?>">
			<span class="itwt-dash__quota-label"><?php esc_html_e( 'โควตา LINE', 'tonkla-warranty-tracker' ); ?></span>
			<span class="itwt-dash__quota-value"><?php echo esc_html( (string) $quota_used . '/' . (string) $quota_total ); ?></span>
			<span class="itwt-dash__quota-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $quota_pct ); ?>">
				<span class="itwt-dash__quota-fill" style="width:<?php echo esc_attr( (string) $quota_pct ); ?>%"></span>
			</span>
		</div>
	</header>

	<div class="itwt-dash-grid itwt-dash-grid--cards">
		<a class="itwt-stat itwt-stat--ok" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&coverage=covered' ) ); ?>">
			<span class="itwt-stat__label"><?php esc_html_e( 'อยู่ในประกัน', 'tonkla-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) $covered ); ?></span>
			<span class="itwt-stat__hint"><?php esc_html_e( 'ความคุ้มครองใช้งานได้', 'tonkla-warranty-tracker' ); ?></span>
		</a>
		<a class="itwt-stat itwt-stat--warn" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&coverage=expiring' ) ); ?>">
			<span class="itwt-stat__label"><?php esc_html_e( 'ใกล้หมด', 'tonkla-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) $expiring ); ?></span>
			<span class="itwt-stat__hint"><?php esc_html_e( 'ควรติดต่อต่อ MA', 'tonkla-warranty-tracker' ); ?></span>
		</a>
		<a class="itwt-stat itwt-stat--bad" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-assets&coverage=expired' ) ); ?>">
			<span class="itwt-stat__label"><?php esc_html_e( 'หมดประกัน', 'tonkla-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) $expired ); ?></span>
			<span class="itwt-stat__hint"><?php esc_html_e( 'ยังไม่มีสัญญาคุ้มครอง', 'tonkla-warranty-tracker' ); ?></span>
		</a>
		<a class="itwt-stat itwt-stat--accent" href="<?php echo esc_url( admin_url( 'admin.php?page=itwt-requests&status=new' ) ); ?>">
			<span class="itwt-stat__label"><?php esc_html_e( 'คำขอใหม่', 'tonkla-warranty-tracker' ); ?></span>
			<span class="itwt-stat__value"><?php echo esc_html( (string) $new_reqs ); ?></span>
			<span class="itwt-stat__hint"><?php esc_html_e( 'รอรับเรื่อง', 'tonkla-warranty-tracker' ); ?></span>
		</a>
	</div>

	<section class="itwt-panel">
		<header class="itwt-panel__head">
			<h2 class="itwt-panel__title"><?php esc_html_e( 'ใกล้หมดภายใน 30 วัน', 'tonkla-warranty-tracker' ); ?></h2>
			<span class="itwt-panel__count"><?php echo esc_html( (string) count( $expiring30 ) ); ?></span>
		</header>
		<div class="itwt-panel__body">
			<table class="itwt-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Service Tag', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'รุ่น', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'หมดวันที่', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'เหลือวัน', 'tonkla-warranty-tracker' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $itwt_render_expiring( $expiring30, $today ); ?>
				</tbody>
			</table>
		</div>
	</section>

	<section class="itwt-panel">
		<header class="itwt-panel__head">
			<h2 class="itwt-panel__title"><?php esc_html_e( 'ใกล้หมดภายใน 60 วัน', 'tonkla-warranty-tracker' ); ?></h2>
			<span class="itwt-panel__count"><?php echo esc_html( (string) count( $expiring60 ) ); ?></span>
		</header>
		<div class="itwt-panel__body">
			<table class="itwt-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Service Tag', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'รุ่น', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'หมดวันที่', 'tonkla-warranty-tracker' ); ?></th>
						<th><?php esc_html_e( 'เหลือวัน', 'tonkla-warranty-tracker' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $itwt_render_expiring( $expiring60, $today ); ?>
				</tbody>
			</table>
		</div>
	</section>
</div>
