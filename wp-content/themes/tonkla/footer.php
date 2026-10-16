<?php
/**
 * Theme footer template.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

$tonkla_footer_list_class = 'space-y-2 text-sm';
?>
<footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 mt-auto">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
			<div class="col-span-2 md:col-span-1">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 mb-4">
					<?php
					$footer_logo_url = carbon_get_theme_option( 'footer_logo' ) ?: carbon_get_theme_option( 'main_logo' );
					if ( $footer_logo_url ) :
						?>
						<img src="<?php echo esc_url( $footer_logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>"
							class="h-8 w-auto brightness-0 invert opacity-80 hover:opacity-100 transition">
					<?php else : ?>
						<img src="<?php echo esc_url( TONKLA_DEFAULT_LOGO ); ?>"
							alt="<?php echo esc_attr( 'Tonkla IT' ); ?>"
							class="h-8 w-auto brightness-0 invert opacity-80 hover:opacity-100 transition">
					<?php endif; ?>
				</a>
				<p class="text-xs text-slate-500">
					<?php
					echo esc_html(
						carbon_get_theme_option( 'footer_description' ) ?: 'ผู้ให้บริการ Cloud Hosting และ Digital Solutions ครบวงจร มุ่งเน้นความเสถียร ปลอดภัย และบริการที่จริงใจ'
					);
					?>
				</p>
			</div>

			<div>
				<h4 class="text-white font-heading font-bold mb-4">บริการ</h4>
				<?php if ( has_nav_menu( 'footer_services' ) ) : ?>
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
				<?php else : ?>
					<ul class="<?php echo esc_attr( $tonkla_footer_list_class ); ?>">
						<li><a href="<?php echo esc_url( home_url( '/domains' ) ); ?>" class="hover:text-brand transition">จดโดเมน</a></li>
						<li><a href="<?php echo esc_url( home_url( '/whois' ) ); ?>" class="hover:text-brand transition">Whois Lookup</a></li>
						<li><a href="<?php echo esc_url( home_url( '/hosting' ) ); ?>" class="hover:text-brand transition">Cloud Hosting</a></li>
						<li><a href="<?php echo esc_url( home_url( '/proxmox' ) ); ?>" class="hover:text-brand transition">Proxmox Server</a></li>
						<li><a href="<?php echo esc_url( home_url( '/dedicated' ) ); ?>" class="hover:text-brand transition">Dedicated Server</a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div>
				<h4 class="text-white font-heading font-bold mb-4">โซลูชัน</h4>
				<?php if ( has_nav_menu( 'footer_solutions' ) ) : ?>
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
				<?php else : ?>
					<ul class="<?php echo esc_attr( $tonkla_footer_list_class ); ?>">
						<li><a href="<?php echo esc_url( home_url( '/pentest' ) ); ?>" class="hover:text-brand transition">Pentest &amp; Fix</a></li>
						<li><a href="<?php echo esc_url( home_url( '/ssl' ) ); ?>" class="hover:text-brand transition">SSL Certificates</a></li>
						<li><a href="<?php echo esc_url( home_url( '/system-implementation' ) ); ?>" class="hover:text-brand transition">System Implementation</a></li>
						<li><a href="<?php echo esc_url( home_url( '/web-dev' ) ); ?>" class="hover:text-brand transition">Laravel Development</a></li>
						<li><a href="<?php echo esc_url( home_url( '/n8n-service' ) ); ?>" class="text-brand font-bold cursor-default">n8n Automation</a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div>
				<h4 class="text-white font-heading font-bold mb-4">สำหรับลูกค้า</h4>
				<?php if ( has_nav_menu( 'footer_customers' ) ) : ?>
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
				<?php else : ?>
					<ul class="<?php echo esc_attr( $tonkla_footer_list_class ); ?>">
						<li><a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="hover:text-brand transition">เข้าสู่ระบบ</a></li>
						<li><a href="<?php echo esc_url( home_url( '/payment' ) ); ?>" class="hover:text-brand transition">แจ้งชำระเงิน</a></li>
						<li><a href="<?php echo esc_url( home_url( '/articles' ) ); ?>" class="hover:text-brand transition">บทความ/ความรู้</a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="hover:text-brand transition">ติดต่อเรา</a></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
			<div>
				<?php
				echo wp_kses_post(
					carbon_get_theme_option( 'copyright_text' ) ?: '&copy; ' . wp_date( 'Y' ) . ' Tonkla IT Co., Ltd. All rights reserved.'
				);
				?>
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
					<a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="hover:text-brand transition">เกี่ยวกับเรา</a>
					<span class="text-slate-700 hidden md:inline">|</span>
					<a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>" class="hover:text-brand transition">นโยบายความเป็นส่วนตัว</a>
					<span class="text-slate-700 hidden md:inline">|</span>
					<a href="<?php echo esc_url( home_url( '/terms' ) ); ?>" class="hover:text-brand transition">เงื่อนไขการให้บริการ</a>
				<?php endif; ?>
			</div>

			<div class="flex gap-4">
				<?php
				foreach ( array( 'facebook', 'twitter', 'instagram' ) as $social ) :
					$url = carbon_get_theme_option( $social . '_url' );
					if ( ! $url ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( $url ); ?>" class="hover:text-white" aria-label="<?php echo esc_attr( ucfirst( $social ) ); ?>">
						<i class="fa-brands fa-<?php echo esc_attr( $social ); ?>" aria-hidden="true"></i>
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
	$pdpa_text     = carbon_get_theme_option( 'pdpa_text' ) ?: 'เราใช้คุกกี้เพื่อเพิ่มประสิทธิภาพและประสบการณ์ที่ดีในการใช้งานเว็บไซต์ คุณสามารถศึกษารายละเอียดได้ที่ <a href="#">นโยบายความเป็นส่วนตัว</a>';
	$pdpa_pos      = carbon_get_theme_option( 'pdpa_position' ) ?: 'bottom-left';
	$accept_text   = carbon_get_theme_option( 'pdpa_accept_text' ) ?: 'ยอมรับทั้งหมด';
	$reject_text   = carbon_get_theme_option( 'pdpa_reject_text' ) ?: 'ปฏิเสธ';
	$pdpa_scripts  = carbon_get_theme_option( 'pdpa_scripts' );
	$policy_link   = '#';
	$policy_assoc  = carbon_get_theme_option( 'pdpa_policy_page' );

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
