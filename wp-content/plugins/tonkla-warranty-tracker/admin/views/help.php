<?php
/**
 * In-plugin help / onboarding.
 *
 * @package ITWT
 */

defined( 'ABSPATH' ) || exit;

$lookup = \ITWT\Settings::lookup_url();
?>
<div class="wrap itwt-wrap itwt-help">
	<header class="itwt-page-header">
		<div>
			<p class="itwt-page-header__eyebrow"><?php esc_html_e( 'IT Warranty Tracker', 'tonkla-warranty-tracker' ); ?></p>
			<h1 class="itwt-page-header__title"><?php esc_html_e( 'คู่มือใช้งาน', 'tonkla-warranty-tracker' ); ?></h1>
		</div>
	</header>
	<p class="itwt-help__intro"><?php esc_html_e( 'สรุปสั้น ๆ สำหรับเริ่มใช้งาน shortcode, QR และการแจ้งเตือน LINE', 'tonkla-warranty-tracker' ); ?></p>

	<section>
		<h2><?php esc_html_e( '1. หน้าเช็คประกัน', 'tonkla-warranty-tracker' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'สร้างหน้า WordPress (เช่น /warranty)', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'ใน Page Attributes → Template เลือก «Warranty (IT Warranty Tracker)»', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'ไปที่ ตั้งค่า → ทั่วไป เลือกหน้านั้นเป็นหน้าเช็คประกัน', 'tonkla-warranty-tracker' ); ?></li>
		</ol>
		<p><?php esc_html_e( 'เทมเพลต Warranty จะจัดเลย์เอาต์เต็มความกว้างโดยปลั๊กอิน — ไม่ต้องใส่ shortcode ก็ได้ (ใส่ [itwt_warranty_lookup] ก็ยังใช้ได้)', 'tonkla-warranty-tracker' ); ?></p>
		<p><?php esc_html_e( 'ทับค่าในเนื้อหาหน้าได้ด้วย:', 'tonkla-warranty-tracker' ); ?></p>
		<p><code>[itwt_warranty_lookup title="เช็คประกัน" desc="กรอก Service Tag หรือ Serial" accent="#0b5cab"]</code></p>
		<?php if ( $lookup ) : ?>
			<p>
				<?php esc_html_e( 'URL หน้าค้นหาปัจจุบัน:', 'tonkla-warranty-tracker' ); ?>
				<a href="<?php echo esc_url( $lookup ); ?>"><?php echo esc_html( $lookup ); ?></a>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'ยังไม่ได้ตั้งหน้า Lookup — ไปที่ ตั้งค่า → ทั่วไป', 'tonkla-warranty-tracker' ); ?></p>
		<?php endif; ?>
	</section>

	<section>
		<h2><?php esc_html_e( '2. สติ๊กเกอร์ QR', 'tonkla-warranty-tracker' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'เปิดเมนู ทรัพย์สิน แล้วติ๊กเครื่องที่ต้องการ', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'เลือก bulk action “พิมพ์สติ๊กเกอร์ที่เลือก” แล้วกด Apply', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'หรือเปิดเมนู พิมพ์สติ๊กเกอร์ แล้วใส่ Asset IDs', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'เลือกขนาด 50×30 หรือ 60×40 มม. แล้วพิมพ์', 'tonkla-warranty-tracker' ); ?></li>
		</ol>
		<p><?php esc_html_e( 'ลูกค้าสแกน QR จะเปิดหน้าค้นหาพร้อม Service Tag อัตโนมัติ', 'tonkla-warranty-tracker' ); ?></p>
	</section>

	<section>
		<h2><?php esc_html_e( '3. แจ้งเตือน LINE', 'tonkla-warranty-tracker' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'ตั้งค่า Channel ใน ตั้งค่า → LINE (Token / Secret จาก LINE Developers)', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'ลูกค้ากดปุ่มรับแจ้งเตือนบนหน้าเช็คประกัน เพื่อขอรหัสผูกบัญชี', 'tonkla-warranty-tracker' ); ?></li>
			<li><?php esc_html_e( 'ระบบส่งแจ้งเตือนใกล้หมดประกันตามโควตาที่ตั้งไว้', 'tonkla-warranty-tracker' ); ?></li>
		</ol>
		<p><?php esc_html_e( 'ซ่อนปุ่ม LINE ได้จาก ตั้งค่า → แสดงผลหน้าบ้าน หากยังไม่พร้อมให้ลูกค้าใช้', 'tonkla-warranty-tracker' ); ?></p>
	</section>

	<section>
		<h2><?php esc_html_e( '4. คำขอแจ้งซ่อม / ต่อ MA', 'tonkla-warranty-tracker' ); ?></h2>
		<p><?php esc_html_e( 'เมื่อลูกค้าส่งคำขอ ระบบแสดงรหัสอ้างอิง เช่น REQ-2026-0042 — เก็บไว้ติดต่อศูนย์บริการ ไม่มีหน้าติดตามสาธารณะ', 'tonkla-warranty-tracker' ); ?></p>
		<p><?php esc_html_e( 'ทีมงานดูรายการในเมนู คำขอ กด “ดูรายละเอียด” เพื่ออัปเดตสถานะ', 'tonkla-warranty-tracker' ); ?></p>
	</section>

	<section>
		<h2><?php esc_html_e( '5. PDPA / ข้อมูลที่แสดงสาธารณะ', 'tonkla-warranty-tracker' ); ?></h2>
		<p><?php esc_html_e( 'หน้าบ้านไม่แสดงชื่อลูกค้าหรือที่ติดตั้ง — เปิด/ปิดได้เฉพาะ S/N ปิดบัง, สเปกสาธารณะ, ประวัติสัญญา, ประวัติบริการ และปุ่มต่าง ๆ ใน ตั้งค่า → แสดงผลหน้าบ้าน', 'tonkla-warranty-tracker' ); ?></p>
	</section>

	<?php if ( \ITWT\Settings::show_developer_credit() ) : ?>
		<section class="itwt-settings-card itwt-help__about">
			<h2><?php esc_html_e( 'เกี่ยวกับผู้พัฒนา', 'tonkla-warranty-tracker' ); ?></h2>
			<div class="itwt-settings-panel">
				<p><?php esc_html_e( 'IT Warranty Tracker พัฒนาโดยต้นกล้า ไอที สำหรับธุรกิจจำหน่ายอุปกรณ์ไอที พัฒนาซอฟต์แวร์ และวางระบบ', 'tonkla-warranty-tracker' ); ?></p>
				<p>
					<a class="itwt-action" href="<?php echo esc_url( \ITWT\Settings::developer_credit_url( 'help' ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'เยี่ยมชมเว็บไซต์ต้นกล้า ไอที', 'tonkla-warranty-tracker' ); ?>
					</a>
				</p>
			</div>
		</section>
	<?php endif; ?>
</div>
