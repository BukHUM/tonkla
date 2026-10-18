<?php
/**
 * Demo import UI markup for Tools screen.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render demo import form HTML for Carbon Fields.
 *
 * @return string
 */
function tonkla_render_demo_import_ui() {
	$groups = array(
		__( 'หน้าอ้างอิง', 'tonkla' )           => array(
			'home'    => array( __( 'หน้าแรก', 'tonkla' ), __( 'หน้าแลนดิ้งหลัก', 'tonkla' ) ),
			'about'   => array( __( 'เกี่ยวกับเรา', 'tonkla' ), __( 'ข้อมูลบริษัทและทีม', 'tonkla' ) ),
			'contact' => array( __( 'ติดต่อเรา', 'tonkla' ), __( 'แผนที่และฟอร์ม', 'tonkla' ) ),
			'works'   => array( __( 'ผลงาน', 'tonkla' ), __( 'พอร์ตโฟลิโอ / เคสศึกษา', 'tonkla' ) ),
		),
		__( 'บริการและผลิตภัณฑ์', 'tonkla' ) => array(
			'web_dev'     => array( __( 'Web Application', 'tonkla' ), __( 'บริการพัฒนาซอฟต์แวร์', 'tonkla' ) ),
			'hosting'     => array( __( 'Web Hosting', 'tonkla' ), __( 'ตารางราคา', 'tonkla' ) ),
			'n8n'         => array( __( 'n8n Automation', 'tonkla' ), __( 'บริการเวิร์กโฟลว์', 'tonkla' ) ),
			'domains'     => array( __( 'โดเมน', 'tonkla' ), __( 'ค้นหาโดเมน', 'tonkla' ) ),
			'pentest'     => array( __( 'Pentest', 'tonkla' ), __( 'บริการด้านความปลอดภัย', 'tonkla' ) ),
			'proxmox'     => array( __( 'Proxmox', 'tonkla' ), __( 'โซลูชันเซิร์ฟเวอร์', 'tonkla' ) ),
			'ssl'         => array( __( 'SSL Certificates', 'tonkla' ), __( 'ความปลอดภัย HTTPS', 'tonkla' ) ),
			'system_impl' => array( __( 'System Implementation', 'tonkla' ), __( 'ติดตั้งระบบ IT', 'tonkla' ) ),
			'mobile_app'  => array( __( 'Mobile App', 'tonkla' ), __( 'พัฒนาแอปมือถือ', 'tonkla' ) ),
		),
		__( 'กฎหมายและคอมโพเนนต์', 'tonkla' ) => array(
			'solutions' => array( __( 'Solutions (Hub)', 'tonkla' ), __( 'ภาพรวมบริการ', 'tonkla' ) ),
			'privacy'   => array( __( 'นโยบายความเป็นส่วนตัว', 'tonkla' ), __( 'เนื้อหา PDPA', 'tonkla' ) ),
			'legal'     => array( __( 'หน้ากฎหมาย', 'tonkla' ), __( 'เงื่อนไขการใช้บริการ', 'tonkla' ) ),
		),
	);

	ob_start();
	?>
	<div class="tonkla-demo-import">
		<p>
			<?php esc_html_e( 'เลือกหน้าที่ต้องการสร้างหรืออัปเดต', 'tonkla' ); ?>
			<br>
			<span class="tonkla-demo-import__warn"><?php esc_html_e( 'หมายเหตุ: หน้าที่มี slug เดียวกันจะถูกเขียนทับ', 'tonkla' ); ?></span>
		</p>

		<div class="tonkla-demo-import__actions">
			<button type="button" class="button" id="demo-select-all"><?php esc_html_e( 'เลือกทั้งหมด', 'tonkla' ); ?></button>
			<button type="button" class="button" id="demo-unselect-all"><?php esc_html_e( 'ยกเลิกทั้งหมด', 'tonkla' ); ?></button>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="tonkla_import_demo">
			<input type="hidden" name="_tonkla_import_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tonkla_import_demo' ) ); ?>">

			<?php foreach ( $groups as $group_label => $pages ) : ?>
				<div class="tonkla-demo-import__group">
					<h4><?php echo esc_html( $group_label ); ?></h4>
					<div class="tonkla-demo-import__grid">
						<?php foreach ( $pages as $value => $meta ) : ?>
							<label>
								<input type="checkbox" class="demo-page-checkbox" name="pages[]" value="<?php echo esc_attr( $value ); ?>" checked>
								<strong><?php echo esc_html( $meta[0] ); ?></strong>
								<span class="tonkla-demo-import__desc"><?php echo esc_html( $meta[1] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<button type="submit" class="button button-primary button-large tonkla-demo-import__submit">
				<?php esc_html_e( 'นำเข้าหน้าที่เลือก', 'tonkla' ); ?>
			</button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
