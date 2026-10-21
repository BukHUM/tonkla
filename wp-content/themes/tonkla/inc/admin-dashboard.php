<?php
/**
 * TONKLA - IT Overview (Dashboard) markup for Carbon Fields HTML field.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a theme option image field has a value.
 *
 * @param string $key Carbon theme option key.
 * @return bool
 */
function tonkla_admin_has_image_option( string $key ): bool {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return false;
	}
	$value = carbon_get_theme_option( $key );
	return ! empty( $value );
}

/**
 * Build readiness checklist items for the Overview dashboard.
 *
 * @return array<int,array{label:string,ok:bool,url:string,hint:string}>
 */
function tonkla_admin_readiness_items(): array {
	$logo_ok   = tonkla_admin_has_image_option( 'main_logo' );
	$phone     = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'phone_number' ) : '';
	$facebook  = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'facebook_url' ) : '';
	$line      = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'line_url' ) : '';
	$contact_ok = ( '' !== trim( $phone ) || '' !== trim( $facebook ) || '' !== trim( $line ) );

	$policy_ok = function_exists( 'tonkla_get_pdpa_policy_page_id' )
		? tonkla_get_pdpa_policy_page_id() > 0
		: false;

	$pll_ok = function_exists( 'pll_the_languages' ) && function_exists( 'pll_languages_list' ) && count( pll_languages_list() ) >= 2;

	return array(
		array(
			'label' => __( 'โลโก้หลัก', 'tonkla' ),
			'ok'    => $logo_ok,
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_SITE ),
			'hint'  => __( 'ไปตั้งค่า', 'tonkla' ),
		),
		array(
			'label' => __( 'ช่องทางติดต่อ / โซเชียล', 'tonkla' ),
			'ok'    => $contact_ok,
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_SITE ),
			'hint'  => __( 'ไปตั้งค่า', 'tonkla' ),
		),
		array(
			'label' => __( 'หน้านโยบายความเป็นส่วนตัว', 'tonkla' ),
			'ok'    => $policy_ok,
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_MARKETING ),
			'hint'  => __( 'ไปตั้งค่า', 'tonkla' ),
		),
		array(
			'label' => __( 'ตัวสลับภาษา (Polylang)', 'tonkla' ),
			'ok'    => $pll_ok,
			'url'   => admin_url( 'admin.php?page=mlang' ),
			'hint'  => $pll_ok ? __( 'พร้อมใช้งาน', 'tonkla' ) : __( 'ตั้งค่าภาษา', 'tonkla' ),
		),
	);
}

/**
 * Plugins the theme depends on, with live status.
 *
 * @return array<int,array{name:string,active:bool,required:bool,url:string}>
 */
function tonkla_admin_plugin_items(): array {
	$plugins = array(
		array(
			'name'     => 'Carbon Fields',
			'active'   => class_exists( '\\Carbon_Fields\\Carbon_Fields' ),
			'required' => true,
			'search'   => 'carbon fields',
		),
		array(
			'name'     => 'Polylang',
			'active'   => function_exists( 'pll_the_languages' ),
			'required' => false,
			'search'   => 'polylang',
		),
		array(
			'name'     => 'Contact Form 7',
			'active'   => defined( 'WPCF7_VERSION' ),
			'required' => false,
			'search'   => 'contact form 7',
		),
	);

	$can_install = current_user_can( 'install_plugins' );
	foreach ( $plugins as &$plugin ) {
		$plugin['url'] = $can_install
			? admin_url( 'plugin-install.php?tab=search&type=term&s=' . rawurlencode( $plugin['search'] ) )
			: '';
		unset( $plugin['search'] );
	}
	unset( $plugin );

	return $plugins;
}

/**
 * Render Overview dashboard HTML.
 *
 * @return string
 */
function tonkla_render_admin_dashboard(): string {
	$theme         = wp_get_theme();
	$php_version   = phpversion();
	$wp_version    = get_bloginfo( 'version' );
	$memory_limit  = ini_get( 'memory_limit' );
	$php_ok        = version_compare( $php_version, '8.1', '>=' );
	$mem_int       = (int) filter_var( $memory_limit, FILTER_SANITIZE_NUMBER_INT );
	$mem_ok        = $mem_int >= 128;
	$plugins       = tonkla_admin_plugin_items();
	$counts        = wp_count_posts( 'newsletter' );
	$nl_active     = isset( $counts->publish ) ? (int) $counts->publish : 0;
	$nl_pending    = isset( $counts->pending ) ? (int) $counts->pending : 0;
	$readiness     = tonkla_admin_readiness_items();

	$shortcuts = array(
		array(
			'label' => __( 'ตั้งค่าเว็บไซต์', 'tonkla' ),
			'desc'  => __( 'ส่วนหัว ส่วนท้าย โซเชียล สารบัญ', 'tonkla' ),
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_SITE ),
			'icon'  => 'dashicons-admin-appearance',
		),
		array(
			'label' => __( 'การตลาดและความเป็นส่วนตัว', 'tonkla' ),
			'desc'  => __( 'คุกกี้ โฆษณา แชท สคริปต์', 'tonkla' ),
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_MARKETING ),
			'icon'  => 'dashicons-megaphone',
		),
		array(
			'label' => __( 'สมาชิกจดหมายข่าว', 'tonkla' ),
			'desc'  => __( 'รายชื่อผู้สมัครรับข่าวสาร', 'tonkla' ),
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_NEWSLETTER ),
			'icon'  => 'dashicons-email-alt',
		),
		array(
			'label' => __( 'เครื่องมือระบบ', 'tonkla' ),
			'desc'  => __( 'นำเข้าเนื้อหา จัดเมนูแอดมิน', 'tonkla' ),
			'url'   => tonkla_admin_page_url( TONKLA_ADMIN_SLUG_TOOLS ),
			'icon'  => 'dashicons-admin-tools',
		),
	);

	ob_start();
	?>
	<div class="tonkla-dashboard">
		<header class="tonkla-dashboard__header">
			<h1 class="tonkla-dashboard__title"><?php esc_html_e( 'ภาพรวม', 'tonkla' ); ?></h1>
			<p class="tonkla-dashboard__meta">
				<?php
				printf(
					/* translators: %s: theme version */
					esc_html__( 'เวอร์ชัน %s · ธีมสำหรับธุรกิจดิจิทัล', 'tonkla' ),
					esc_html( $theme->get( 'Version' ) )
				);
				?>
			</p>
		</header>

		<div class="tonkla-dashboard__grid">
			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'ความพร้อมของเว็บไซต์', 'tonkla' ); ?>
				</h2>
				<ul class="tonkla-dashboard__list">
					<?php foreach ( $readiness as $item ) : ?>
						<li>
							<span class="tonkla-dashboard__label"><?php echo esc_html( $item['label'] ); ?></span>
							<span class="tonkla-dashboard__status-wrap">
								<?php if ( $item['ok'] ) : ?>
									<span class="tonkla-dashboard__value is-ok">
										<span class="dashicons dashicons-yes" aria-hidden="true"></span>
										<?php esc_html_e( 'พร้อม', 'tonkla' ); ?>
									</span>
								<?php else : ?>
									<a class="tonkla-dashboard__value is-warn tonkla-dashboard__link" href="<?php echo esc_url( $item['url'] ); ?>">
										<span class="dashicons dashicons-warning" aria-hidden="true"></span>
										<?php echo esc_html( $item['hint'] ); ?>
									</a>
								<?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
					<?php esc_html_e( 'ทางลัด', 'tonkla' ); ?>
				</h2>
				<div class="tonkla-dashboard__links">
					<?php foreach ( $shortcuts as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>">
							<span class="dashicons <?php echo esc_attr( $link['icon'] ); ?>" aria-hidden="true"></span>
							<span>
								<strong><?php echo esc_html( $link['label'] ); ?></strong>
								<span class="tonkla-dashboard__link-desc"><?php echo esc_html( $link['desc'] ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-desktop" aria-hidden="true"></span>
					<?php esc_html_e( 'สถานะระบบ', 'tonkla' ); ?>
				</h2>
				<ul class="tonkla-dashboard__list">
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'ธีม Tonkla - Theme', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value"><?php echo esc_html( (string) $theme->get( 'Version' ) ); ?></span>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'WordPress', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value"><?php echo esc_html( $wp_version ); ?></span>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'PHP', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value <?php echo esc_attr( $php_ok ? 'is-ok' : 'is-bad' ); ?>">
							<?php echo esc_html( $php_version ); ?>
						</span>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'Memory Limit', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value <?php echo esc_attr( $mem_ok ? 'is-ok' : 'is-warn' ); ?>">
							<?php echo esc_html( $memory_limit ); ?>
						</span>
					</li>
				</ul>
			</section>

			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>
					<?php esc_html_e( 'ปลั๊กอินที่ธีมใช้', 'tonkla' ); ?>
				</h2>
				<ul class="tonkla-dashboard__list">
					<?php foreach ( $plugins as $plugin ) : ?>
						<?php
						if ( $plugin['active'] ) {
							$status_class = 'is-ok';
							$status_icon  = 'dashicons-yes';
							$status_text  = __( 'ใช้งานอยู่', 'tonkla' );
						} elseif ( $plugin['required'] ) {
							$status_class = 'is-bad';
							$status_icon  = 'dashicons-no';
							$status_text  = __( 'ต้องติดตั้ง', 'tonkla' );
						} else {
							$status_class = 'is-warn';
							$status_icon  = 'dashicons-warning';
							$status_text  = __( 'แนะนำให้ติดตั้ง', 'tonkla' );
						}
						?>
						<li>
							<span class="tonkla-dashboard__label">
								<?php echo esc_html( $plugin['name'] ); ?>
								<?php if ( $plugin['required'] ) : ?>
									<span class="tonkla-dashboard__tag"><?php esc_html_e( 'จำเป็น', 'tonkla' ); ?></span>
								<?php endif; ?>
							</span>
							<?php if ( ! $plugin['active'] && '' !== $plugin['url'] ) : ?>
								<a class="tonkla-dashboard__value tonkla-dashboard__link <?php echo esc_attr( $status_class ); ?>" href="<?php echo esc_url( $plugin['url'] ); ?>">
									<span class="dashicons <?php echo esc_attr( $status_icon ); ?>" aria-hidden="true"></span>
									<?php echo esc_html( $status_text ); ?>
								</a>
							<?php else : ?>
								<span class="tonkla-dashboard__value <?php echo esc_attr( $status_class ); ?>">
									<span class="dashicons <?php echo esc_attr( $status_icon ); ?>" aria-hidden="true"></span>
									<?php echo esc_html( $status_text ); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-media-text" aria-hidden="true"></span>
					<?php esc_html_e( 'สัญญาอนุญาต (License)', 'tonkla' ); ?>
				</h2>
				<ul class="tonkla-dashboard__list">
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'สัญญาอนุญาต', 'tonkla' ); ?></span>
						<a class="tonkla-dashboard__value tonkla-dashboard__link" href="https://www.gnu.org/licenses/gpl-2.0.html" target="_blank" rel="noopener">
							GNU GPL v2 or later
						</a>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'ลิขสิทธิ์', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'ต้นกล้า ไอที', 'tonkla' ); ?></span>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'ผู้พัฒนา', 'tonkla' ); ?></span>
						<a class="tonkla-dashboard__value tonkla-dashboard__link" href="https://tonkla.co" target="_blank" rel="noopener">tonkla.co</a>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'ส่วนประกอบบุคคลที่สาม', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value"><?php esc_html_e( 'Font Awesome, Alpine.js, Tailwind CSS (MIT/OFL) — ดูรายละเอียดใน THIRD-PARTY-NOTICES.md', 'tonkla' ); ?></span>
					</li>
				</ul>
				<p class="tonkla-dashboard__card-footer">
					<?php esc_html_e( 'ข้อความสัญญาอนุญาตฉบับเต็มอยู่ในไฟล์ LICENSE ในโฟลเดอร์ธีม และที่', 'tonkla' ); ?>
					<a href="https://www.gnu.org/licenses/gpl-2.0.html" target="_blank" rel="noopener">gnu.org/licenses/gpl-2.0.html</a>
				</p>
			</section>

			<section class="tonkla-dashboard__card">
				<h2>
					<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'จดหมายข่าว', 'tonkla' ); ?>
				</h2>
				<ul class="tonkla-dashboard__list">
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'ยืนยันแล้ว', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value is-ok"><?php echo esc_html( (string) $nl_active ); ?></span>
					</li>
					<li>
						<span class="tonkla-dashboard__label"><?php esc_html_e( 'รอยืนยัน', 'tonkla' ); ?></span>
						<span class="tonkla-dashboard__value <?php echo $nl_pending > 0 ? 'is-warn' : ''; ?>">
							<?php echo esc_html( (string) $nl_pending ); ?>
						</span>
					</li>
				</ul>
				<p class="tonkla-dashboard__card-footer">
					<a href="<?php echo esc_url( tonkla_admin_page_url( TONKLA_ADMIN_SLUG_NEWSLETTER ) ); ?>">
						<?php esc_html_e( 'ดูรายชื่อทั้งหมด', 'tonkla' ); ?> &rarr;
					</a>
				</p>
			</section>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
