<?php
/**
 * Theme footer template.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

$tonkla_footer_list_class = 'space-y-2 text-sm';
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
$tonkla_grid_class        = 'grid grid-cols-2 gap-8 mb-8 ' . ( $tonkla_grid_md[ $tonkla_col_count ] ?? 'md:grid-cols-4' );
?>
<footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 mt-auto">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="<?php echo esc_attr( $tonkla_grid_class ); ?>">
			<div class="<?php echo esc_attr( $tonkla_col_count > 1 ? 'col-span-2 md:col-span-1' : 'col-span-2' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 mb-4">
					<?php tonkla_the_logo( 'footer', 'h-8 w-auto brightness-0 invert opacity-80 hover:opacity-100 transition' ); ?>
				</a>
				<?php if ( '' !== $tonkla_footer_desc ) : ?>
					<p class="text-xs text-slate-400">
						<?php echo esc_html( $tonkla_footer_desc ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $tonkla_has_services ) : ?>
				<div>
					<p class="text-white font-heading font-bold mb-4"><?php esc_html_e( 'บริการ', 'tonkla' ); ?></p>
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
					<p class="text-white font-heading font-bold mb-4"><?php esc_html_e( 'โซลูชัน', 'tonkla' ); ?></p>
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
					<p class="text-white font-heading font-bold mb-4"><?php esc_html_e( 'สำหรับลูกค้า', 'tonkla' ); ?></p>
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

		<div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
			<div>
				<?php echo wp_kses_post( tonkla_get_copyright_text() ); ?>

				<?php
				/** Filter: return false to hide the theme credit regardless of the setting. */
				if ( apply_filters( 'tonkla_show_footer_credit', (bool) carbon_get_theme_option( 'footer_show_credit' ) ) ) :
					?>
					<span class="block mt-1 md:mt-0 md:inline text-xs text-slate-400">
						<span class="hidden md:inline text-slate-600" aria-hidden="true">&middot;</span>
						<?php esc_html_e( 'พัฒนาโดย', 'tonkla' ); ?>
						<a href="<?php echo esc_url( 'https://tonkla.co' ); ?>" class="text-slate-300 hover:text-brand hover:underline transition" target="_blank" rel="noopener"><?php esc_html_e( 'ต้นกล้า ไอที', 'tonkla' ); ?></a>
					</span>
				<?php endif; ?>
			</div>

			<div class="flex flex-wrap gap-4 justify-center">
				<?php if ( has_nav_menu( 'footer_legal' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_legal',
							'container'      => false,
							'menu_class'     => 'flex flex-wrap gap-4 justify-center',
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

			<div class="flex gap-4">
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
					<a href="<?php echo esc_url( $url ); ?>" class="hover:text-brand transition" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $aria ); ?>">
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
	$policy_link  = '#';
	$policy_assoc = carbon_get_theme_option( 'pdpa_policy_page' );

	if ( $policy_assoc && ! empty( $policy_assoc[0] ) ) {
		$policy_link = get_permalink( $policy_assoc[0]['id'] );
	}

	$container_classes = 'fixed z-[60] bg-white border border-slate-200 transition-transform duration-500 ';
	$pdpa_slide_off    = 'translate-y-[150%]';
	$translate_class   = $pdpa_slide_off;

	switch ( $pdpa_pos ) {
		case 'bottom-right':
			$container_classes .= 'bottom-6 right-6 w-full md:w-96 rounded-none md:rounded-2xl p-6';
			break;
		case 'bottom-bar':
			$container_classes .= 'bottom-0 left-0 w-full p-6 border-t border-slate-200';
			$translate_class    = 'translate-y-full';
			break;
		case 'center':
			$container_classes .= 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg rounded-2xl p-8 opacity-0 pointer-events-none scale-95';
			$translate_class    = 'opacity-0 pointer-events-none scale-95';
			break;
		case 'bottom-left':
		default:
			$container_classes .= 'bottom-6 left-6 w-full md:w-96 rounded-none md:rounded-2xl p-6';
			break;
	}
	?>

	<?php if ( $pdpa_scripts ) : ?>
		<script type="text/plain" data-type="pdpa-script" id="pdpa-scripts-template">
		<?php echo $pdpa_scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-controlled deferred scripts loaded after consent. ?>
		</script>
	<?php endif; ?>

	<div id="pdpa-banner" data-position="<?php echo esc_attr( $pdpa_pos ); ?>"
		class="<?php echo esc_attr( $container_classes . ' ' . $translate_class ); ?>">

		<?php if ( 'center' === $pdpa_pos ) : ?>
			<div id="pdpa-backdrop" class="fixed inset-0 bg-slate-900/50 -z-10 absolute"></div>
		<?php endif; ?>

		<div class="flex <?php echo ( 'bottom-bar' === $pdpa_pos ) ? 'flex-col md:flex-row items-center justify-between gap-6' : 'flex-col items-start gap-4'; ?>">
			<div class="flex items-start gap-4">
				<div class="text-3xl text-brand shrink-0" aria-hidden="true">
					<i class="fa-solid fa-cookie-bite"></i>
				</div>
				<div>
					<h4 class="font-bold text-slate-900 mb-2">เว็บไซต์นี้ใช้คุกกี้</h4>
					<div class="text-xs text-slate-500 leading-relaxed pdpa-content [&_a]:text-brand [&_a]:underline">
						<?php
						if ( '#' !== $policy_link ) {
							$pdpa_text = str_replace( 'href="#"', 'href="' . esc_url( $policy_link ) . '"', $pdpa_text );
						}
						echo wp_kses_post( $pdpa_text );

						if ( '#' !== $policy_link && false === strpos( $pdpa_text, 'href' ) ) :
							?>
							<a href="<?php echo esc_url( $policy_link ); ?>" class="text-brand hover:underline">นโยบายความเป็นส่วนตัว</a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="flex gap-2 shrink-0 <?php echo ( 'bottom-bar' === $pdpa_pos ) ? 'mt-4 md:mt-0' : 'mt-2'; ?>">
				<button id="pdpa-accept-btn" type="button"
					class="px-6 py-2.5 bg-brand hover:bg-brand-dark text-white text-sm font-bold rounded-lg transition border border-brand">
					<?php echo esc_html( $accept_text ); ?>
				</button>
				<button id="pdpa-reject-btn" type="button"
					class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-lg transition border border-slate-200">
					<?php echo esc_html( $reject_text ); ?>
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
