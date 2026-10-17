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

$name = Settings::developer_name();
$url  = Settings::developer_credit_url( $credit_context );
$mod  = 'itwt-developer-credit--' . $credit_context;
?>
<p class="itwt-developer-credit itwt-no-print <?php echo esc_attr( $mod ); ?>">
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %s: developer name as HTML link */
			__( 'พัฒนาโดย %s', 'it-warranty-tracker' ),
			'<a class="itwt-developer-credit__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $name ) . '</a>'
		),
		array(
			'a' => array(
				'class'  => true,
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	);
	?>
</p>
