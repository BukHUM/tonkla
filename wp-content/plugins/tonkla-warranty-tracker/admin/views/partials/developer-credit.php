<?php
/**
 * Developer credit / backlink partial.
 *
 * @package ITWT
 * @var string $credit_context admin|portal|help
 */

defined( 'ABSPATH' ) || exit;

use ITWT\Settings;

$credit_context = isset( $credit_context ) ? sanitize_key( (string) $credit_context ) : 'admin';
if ( ! in_array( $credit_context, array( 'admin', 'portal', 'help' ), true ) ) {
	$credit_context = 'admin';
}

$name        = Settings::developer_name();
$url         = Settings::developer_credit_url( $credit_context );
$mod         = 'itwt-developer-credit--' . $credit_context;
$year        = gmdate( 'Y' );
$license_url = 'https://www.gnu.org/licenses/old-licenses/gpl-2.0.html';

$link_kses = array(
	'a' => array(
		'class'  => true,
		'href'   => true,
		'target' => true,
		'rel'    => true,
	),
);
?>
<div class="itwt-developer-credit itwt-no-print <?php echo esc_attr( $mod ); ?>">
	<p class="itwt-developer-credit__byline">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %s: developer name as HTML link */
				__( 'พัฒนาโดย %s', 'tonkla-warranty-tracker' ),
				'<a class="itwt-developer-credit__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $name ) . '</a>'
			),
			$link_kses
		);
		?>
	</p>
	<?php if ( 'help' !== $credit_context ) : ?>
		<p class="itwt-developer-credit__license">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: 1: year, 2: GPL license link label */
					__( '© %1$s ต้นกล้า ไอที — แจกฟรีภายใต้สัญญาอนุญาต %2$s อนุญาตให้ใช้ แก้ไข และแจกจ่ายต่อได้ โดยต้องคงเครดิตและข้อความลิขสิทธิ์นี้ไว้', 'tonkla-warranty-tracker' ),
					esc_html( $year ),
					'<a class="itwt-developer-credit__link" href="' . esc_url( $license_url ) . '" target="_blank" rel="noopener noreferrer">GPL v2 หรือใหม่กว่า</a>'
				),
				$link_kses
			);
			?>
		</p>
	<?php endif; ?>
</div>
