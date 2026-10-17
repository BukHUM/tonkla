<?php
/**
 * Page Builder module registry.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registry of page builder modules (slug => meta).
 *
 * @return array<string,array<string,string>>
 */
function tonkla_builder_modules() {
	$uri = get_template_directory_uri() . '/assets/img/builder';

	return array(
		'hero_module'         => array(
			'label'       => __( 'Hero Section', 'tonkla' ),
			'description' => __( 'แบนเนอร์หัวหน้า พร้อมปุ่มและพื้นหลัง', 'tonkla' ),
			'wireframe'   => $uri . '/hero.svg',
			'header'      => 'Hero: <%- badge || section_title || "Section" %>',
		),
		'service_grid'        => array(
			'label'       => __( 'Service Grid', 'tonkla' ),
			'description' => __( 'การ์ดบริการ / ฟีเจอร์ แบบตาราง', 'tonkla' ),
			'wireframe'   => $uri . '/service-grid.svg',
			'header'      => 'Service Grid: <%- section_title || "Untitled" %>',
		),
		'tech_stack_grid'     => array(
			'label'       => __( 'Tech Stack', 'tonkla' ),
			'description' => __( 'แสดงเทคโนโลยี / โลโก้เครื่องมือ', 'tonkla' ),
			'wireframe'   => $uri . '/tech-stack.svg',
			'header'      => 'Tech Stack: <%- section_title || "Untitled" %>',
		),
		'portfolio_module'    => array(
			'label'       => __( 'Portfolio', 'tonkla' ),
			'description' => __( 'แสดงผลงาน / โปรเจกต์ พร้อมรูปและลิงก์', 'tonkla' ),
			'wireframe'   => $uri . '/portfolio.svg',
			'header'      => 'Portfolio: <%- section_title || "Untitled" %>',
		),
		'pricing_table'       => array(
			'label'       => __( 'Pricing Table', 'tonkla' ),
			'description' => __( 'ตารางเปรียบเทียบแพ็กเกจราคา', 'tonkla' ),
			'wireframe'   => $uri . '/pricing.svg',
			'header'      => 'Pricing: <%- section_title || "Untitled" %>',
		),
		'feature_split'       => array(
			'label'       => __( 'Feature Split (Software)', 'tonkla' ),
			'description' => __( 'รายการฟีเจอร์ซ้าย–ขวา พร้อมภาพ/โค้ด', 'tonkla' ),
			'wireframe'   => $uri . '/feature-split.svg',
			'header'      => 'Feature Split: <%- section_title || "Untitled" %>',
		),
		'content_block'       => array(
			'label'       => __( 'Content Block', 'tonkla' ),
			'description' => __( 'บล็อกข้อความ + รูปหรือโค้ด', 'tonkla' ),
			'wireframe'   => $uri . '/content-block.svg',
			'header'      => 'Content Block',
		),
		'cta_module'          => array(
			'label'       => __( 'Call to Action', 'tonkla' ),
			'description' => __( 'กล่องเรียกให้คลิกปุ่มสำคัญ', 'tonkla' ),
			'wireframe'   => $uri . '/cta.svg',
			'header'      => 'CTA: <%- title || "Untitled" %>',
		),
		'workflow_module'     => array(
			'label'       => __( 'n8n Workflow', 'tonkla' ),
			'description' => __( 'ขั้นตอน workflow แนวนอน', 'tonkla' ),
			'wireframe'   => $uri . '/workflow.svg',
			'header'      => 'Workflow: <%- headline || "Untitled" %>',
		),
		'stats_module'        => array(
			'label'       => __( 'Stats / Counter', 'tonkla' ),
			'description' => __( 'ตัวเลขสถิติสำคัญ', 'tonkla' ),
			'wireframe'   => $uri . '/stats.svg',
			'header'      => 'Stats',
		),
		'process_module'      => array(
			'label'       => __( 'Process Timeline', 'tonkla' ),
			'description' => __( 'ขั้นตอนการทำงานเรียงตามลำดับ', 'tonkla' ),
			'wireframe'   => $uri . '/process.svg',
			'header'      => 'Process: <%- section_title || "Untitled" %>',
		),
		'faq_module'          => array(
			'label'       => __( 'FAQ', 'tonkla' ),
			'description' => __( 'คำถาม–คำตอบแบบพับได้', 'tonkla' ),
			'wireframe'   => $uri . '/faq.svg',
			'header'      => 'FAQ: <%- section_title || "Untitled" %>',
		),
		'testimonials_module' => array(
			'label'       => __( 'Testimonials', 'tonkla' ),
			'description' => __( 'รีวิว / เสียงจากลูกค้า', 'tonkla' ),
			'wireframe'   => $uri . '/testimonials.svg',
			'header'      => 'Testimonials: <%- section_title || "Untitled" %>',
		),
		'contact_section'     => array(
			'label'       => __( 'Contact Section', 'tonkla' ),
			'description' => __( 'ฟอร์มติดต่อ + ข้อมูลบริษัท', 'tonkla' ),
			'wireframe'   => $uri . '/contact.svg',
			'header'      => 'Contact: <%- info_title || "Untitled" %>',
		),
		'map_faq_section'     => array(
			'label'       => __( 'Map & FAQ', 'tonkla' ),
			'description' => __( 'แผนที่ Google + FAQ คู่กัน', 'tonkla' ),
			'wireframe'   => $uri . '/map-faq.svg',
			'header'      => 'Map & FAQ: <%- faq_title || "Untitled" %>',
		),
	);
}

/**
 * Module meta by slug.
 *
 * @param string $slug Module slug.
 * @return array<string,string>|null
 */
function tonkla_builder_module( $slug ) {
	$modules = tonkla_builder_modules();
	return isset( $modules[ $slug ] ) ? $modules[ $slug ] : null;
}
