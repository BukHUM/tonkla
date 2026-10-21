<?php
/**
 * Theme footer template.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

$tonkla_footer_list_class = 'tonkla-footer-menu space-y-2.5 text-sm';
$tonkla_footer_desc       = tonkla_get_footer_description();
$tonkla_has_services      = has_nav_menu( 'footer_services' );
$tonkla_has_solutions     = has_nav_menu( 'footer_solutions' );
$tonkla_has_customers     = has_nav_menu( 'footer_customers' );
$tonkla_col_count         = 1 + (int) $tonkla_has_services + (int) $tonkla_has_solutions + (int) $tonkla_has_customers;
$tonkla_grid_md           = array(
	1 => 'md:grid-cols-1',
	2 => 'md:grid-cols-2',
	3 => 'md:grid-cols-3',
	4 => 'md:grid-cols-4',
);
$tonkla_grid_class        = 'grid grid-cols-2 gap-8 mb-10 ' . ( $tonkla_grid_md[ $tonkla_col_count ] ?? 'md:grid-cols-4' );
?>
<footer class="tonkla-site-footer bg-slate-950 text-slate-400 mt-auto">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-14">
		<div class="<?php echo esc_attr( $tonkla_grid_class ); ?>">
			<div class="<?php echo esc_attr( $tonkla_col_count > 1 ? 'col-span-2 md:col-span-1' : 'col-span-2' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center gap-2 mb-4">
					<?php tonkla_the_logo( 'footer', 'h-8 w-auto brightness-0 invert opacity-90 hover:opacity-100 transition' ); ?>
				</a>
				<?php if ( '' !== $tonkla_footer_desc ) : ?>
					<p class="text-xs text-slate-500 leading-relaxed max-w-xs">
						<?php echo esc_html( $tonkla_footer_desc ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $tonkla_has_services ) : ?>
				<div>
					<p class="text-white font-heading font-bold mb-4 text-sm tracking-wide"><?php esc_html_e( 'บริการ', 'tonkla' ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_services',
							'container'      => false,
							'menu_class'     => $tonkla_footer_list_class,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $tonkla_has_solutions ) : ?>
				<div>
					<p class="text-white font-heading font-bold mb-4 text-sm tracking-wide"><?php esc_html_e( 'โซลูชัน', 'tonkla' ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_solutions',
							'container'      => false,
							'menu_class'     => $tonkla_footer_list_class,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $tonkla_has_customers ) : ?>
				<div>
					<p class="text-white font-heading font-bold mb-4 text-sm tracking-wide"><?php esc_html_e( 'สำหรับลูกค้า', 'tonkla' ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_customers',
							'container'      => false,
							'menu_class'     => $tonkla_footer_list_class,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>

		<div class="border-t border-slate-800/80 pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
			<div class="text-center md:text-left">
				<?php echo wp_kses_post( tonkla_get_copyright_text() ); ?>

				<?php
				/**
				 * Developer credit is a condition of the free license and is always
				 * shown. The filter remains as a code-level escape hatch for
				 * licensed/commercial builds only; there is no admin off switch.
				 */
				if ( apply_filters( 'tonkla_show_footer_credit', true ) ) :
					?>
					<span class="block mt-1 md:mt-0 md:inline text-xs text-slate-500">
						<span class="hidden md:inline text-slate-700" aria-hidden="true">&middot;</span>
						<?php esc_html_e( 'พัฒนาโดย', 'tonkla' ); ?>
						<a href="<?php echo esc_url( 'https://tonkla.co' ); ?>" class="text-slate-400 hover:text-brand hover:underline transition" target="_blank" rel="noopener"><?php esc_html_e( 'ต้นกล้า ไอที', 'tonkla' ); ?></a>
					</span>
				<?php endif; ?>
			</div>

			<div class="flex flex-wrap gap-x-5 gap-y-2 justify-center text-xs text-slate-500">
				<?php if ( has_nav_menu( 'footer_legal' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_legal',
							'container'      => false,
							'menu_class'     => 'tonkla-footer-legal flex flex-wrap gap-x-5 gap-y-2 justify-center',
							'fallback_cb'    => false,
						)
					);
					?>
				<?php else : ?>
					<?php
					$tonkla_privacy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
					if ( $tonkla_privacy_url ) :
						?>
						<a href="<?php echo esc_url( $tonkla_privacy_url ); ?>" class="hover:text-brand transition"><?php esc_html_e( 'นโยบายความเป็นส่วนตัว', 'tonkla' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="flex gap-3">
				<?php
				$tonkla_social_icons = array(
					'facebook'  => 'facebook',
					'twitter'   => 'x-twitter',
					'instagram' => 'instagram',
					'line'      => 'line',
				);
				foreach ( $tonkla_social_icons as $social_key => $icon_slug ) :
					$url = carbon_get_theme_option( $social_key . '_url' );
					if ( ! $url ) {
						continue;
					}
					$aria = 'line' === $social_key ? 'LINE' : ucfirst( $social_key );
					if ( 'twitter' === $social_key ) {
						$aria = 'X';
					}
					?>
					<a href="<?php echo esc_url( $url ); ?>"
						class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-brand/40 hover:text-brand hover:bg-brand/10 transition"
						target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $aria ); ?>">
						<i class="fa-brands fa-<?php echo esc_attr( $icon_slug ); ?>" aria-hidden="true"></i>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>

<?php if ( carbon_get_theme_option( 'enable_back_to_top' ) ) : ?>
	<button id="back-to-top" type="button"
		class="fixed bottom-6 right-6 bg-brand hover:bg-brand-dark text-white p-3 rounded-full border border-brand transition-all duration-300 opacity-0 invisible translate-y-4 z-50 focus-visible:border-white group"
		aria-label="<?php echo esc_attr__( 'Back to top', 'tonkla' ); ?>">
		<i class="fa-solid fa-arrow-up text-base group-hover:animate-bounce" aria-hidden="true"></i>
	</button>
<?php endif; ?>

<?php
if ( carbon_get_theme_option( 'pdpa_enable' ) ) :
	$pdpa_text    = carbon_get_theme_option( 'pdpa_text' ) ?: 'เราใช้คุกกี้เพื่อเพิ่มประสิทธิภาพและประสบการณ์ที่ดีในการใช้งานเว็บไซต์ คุณสามารถศึกษารายละเอียดได้ที่ <a href="#">นโยบายความเป็นส่วนตัว</a>';
	$pdpa_pos     = carbon_get_theme_option( 'pdpa_position' ) ?: 'bottom-left';
	$accept_text  = carbon_get_theme_option( 'pdpa_accept_text' ) ?: 'ยอมรับทั้งหมด';
	$reject_text  = carbon_get_theme_option( 'pdpa_reject_text' ) ?: 'ปฏิเสธ';
	$pdpa_scripts = carbon_get_theme_option( 'pdpa_scripts' );
	// Skip placeholder Analytics ID from theme defaults — loads ~180KiB of dead gtag.
	if ( is_string( $pdpa_scripts ) && false !== strpos( $pdpa_scripts, 'UA-XXXXX-Y' ) ) {
		$pdpa_scripts = '';
	}
	$policy_link = '#';
	$policy_url  = function_exists( 'tonkla_get_pdpa_policy_url' ) ? tonkla_get_pdpa_policy_url() : '';
	if ( '' !== $policy_url ) {
		$policy_link = $policy_url;
	}
	if ( '#' !== $policy_link ) {
		$pdpa_text = str_replace( 'href="#"', 'href="' . esc_url( $policy_link ) . '"', $pdpa_text );
	}

	$is_center     = ( 'center' === $pdpa_pos );
	$is_bottom_bar = ( 'bottom-bar' === $pdpa_pos );

	$container_classes = 'fixed z-[60] bg-white border border-slate-200 shadow-lg transition duration-300 ease-out ';
	$hidden_class      = 'translate-y-[150%]';

	switch ( $pdpa_pos ) {
		case 'bottom-right':
			$container_classes .= 'bottom-6 right-6 left-6 sm:left-auto w-auto sm:w-96 rounded-xl p-5';
			break;
		case 'bottom-bar':
			$container_classes .= 'bottom-0 left-0 w-full rounded-none p-4 sm:p-5 border-x-0 border-b-0 shadow-[0_-4px_24px_rgba(15,23,42,0.06)]';
			$hidden_class       = 'translate-y-full';
			break;
		case 'center':
			// Dialog card — backdrop is a sibling below.
			$container_classes .= 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] max-w-md rounded-2xl p-6 sm:p-7 shadow-2xl shadow-slate-900/15';
			$hidden_class       = 'opacity-0 pointer-events-none scale-95';
			break;
		case 'bottom-left':
		default:
			$container_classes .= 'bottom-6 left-6 right-6 sm:right-auto w-auto sm:w-96 rounded-xl p-5';
			break;
	}
	?>

	<?php if ( $pdpa_scripts ) : ?>
		<script type="text/plain" data-type="pdpa-script" id="pdpa-scripts-template">
		<?php echo $pdpa_scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-controlled deferred scripts loaded after consent. ?>
		</script>
	<?php endif; ?>

	<?php if ( $is_center ) : ?>
		<div id="pdpa-backdrop"
			class="fixed inset-0 z-[59] bg-slate-900/40 opacity-0 pointer-events-none transition-opacity duration-300"
			aria-hidden="true"></div>
	<?php endif; ?>

	<div id="pdpa-banner"
		data-position="<?php echo esc_attr( $pdpa_pos ); ?>"
		class="<?php echo esc_attr( $container_classes . ' ' . $hidden_class ); ?>"
		<?php echo $is_center ? 'role="dialog" aria-modal="true" aria-labelledby="pdpa-title"' : 'role="region" aria-label="' . esc_attr__( 'ยินยอมคุกกี้', 'tonkla' ) . '"'; ?>>

		<div class="flex <?php echo $is_bottom_bar ? 'flex-col sm:flex-row sm:items-center sm:justify-between gap-4' : 'flex-col gap-5'; ?>">
			<div class="flex items-start gap-3.5 min-w-0">
				<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand" aria-hidden="true">
					<i class="fa-solid fa-cookie-bite text-lg"></i>
				</div>
				<div class="min-w-0 pt-0.5">
					<h4 id="pdpa-title" class="font-heading font-bold text-slate-900 text-base leading-snug mb-1.5">
						<?php esc_html_e( 'เว็บไซต์นี้ใช้คุกกี้', 'tonkla' ); ?>
					</h4>
					<div class="text-sm text-slate-600 leading-relaxed pdpa-content [&_a]:text-brand [&_a]:underline [&_a]:font-medium">
						<?php
						echo wp_kses_post( $pdpa_text );
						if ( '#' !== $policy_link && false === strpos( $pdpa_text, 'href' ) ) :
							?>
							<a href="<?php echo esc_url( $policy_link ); ?>" class="text-brand hover:underline"><?php esc_html_e( 'นโยบายความเป็นส่วนตัว', 'tonkla' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="flex <?php echo $is_center ? 'flex-col-reverse sm:flex-row' : ( $is_bottom_bar ? 'flex-row' : 'flex-row' ); ?> gap-2 shrink-0 w-full <?php echo $is_bottom_bar ? 'sm:w-auto' : ''; ?>">
				<button id="pdpa-reject-btn" type="button"
					class="<?php echo $is_center ? 'flex-1' : ''; ?> px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg transition border border-slate-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-400">
					<?php echo esc_html( $reject_text ); ?>
				</button>
				<button id="pdpa-accept-btn" type="button"
					class="<?php echo $is_center ? 'flex-1' : ''; ?> px-5 py-2.5 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg transition border border-brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
					<?php echo esc_html( $accept_text ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
endif;

get_template_part( 'parts/chat/customer-service' );

wp_footer();
?>
</body>
</html>
