<?php
/**
 * Page Builder post meta fields (Flexible modules).
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Register the Flexible Page Builder meta box.
 */
function tonkla_register_page_builder_fields() {
	$modules = tonkla_builder_modules();

	// Accordion (grid + collapsed): click header to expand one module — less scrolling.
	$complex = Field::make( 'complex', 'page_modules', __( 'โมดูล', 'tonkla' ) )
		->set_layout( 'grid' )
		->set_collapsed( true )
		->set_classes( 'tonkla-page-modules' );

	// —— Hero ——
	$hero_buttons = array_merge(
		array(
			Field::make( 'text', 'text', __( 'ข้อความปุ่ม', 'tonkla' ) ),
		),
		tonkla_icon_fields(
			array(
				'class_key'     => 'icon',
				'default_color' => '',
				'color_options' => array_merge( array( '' => __( 'ตามปุ่ม (ค่าเริ่มต้น)', 'tonkla' ) ), tonkla_icon_colors() ),
			)
		),
		tonkla_link_fields(),
		array(
			Field::make( 'select', 'style', __( 'สไตล์ปุ่ม', 'tonkla' ) )
				->add_options(
					array(
						'primary'   => __( 'หลัก (Brand)', 'tonkla' ),
						'secondary' => __( 'รอง (ขอบโปร่ง)', 'tonkla' ),
						'dark'      => __( 'เข้ม', 'tonkla' ),
					)
				),
		)
	);

	$complex->add_fields(
		'hero_module',
		$modules['hero_module']['label'],
		array_merge(
			array( tonkla_module_info_field( 'hero_module' ) ),
			array(
				Field::make( 'separator', 'sep_hero_content', __( 'เนื้อหา', 'tonkla' ) ),
				Field::make( 'select', 'hero_type', __( 'รูปแบบ Hero', 'tonkla' ) )
					->add_options(
						array(
							'home'        => __( 'หน้าแรก (กึ่งกลาง)', 'tonkla' ),
							'service'     => __( 'บริการ (กราฟิกไอคอน)', 'tonkla' ),
							'service_alt' => __( 'บริการ (บล็อกโค้ด)', 'tonkla' ),
						)
					),
				Field::make( 'text', 'badge', __( 'ป้าย Badge', 'tonkla' ) ),
				Field::make( 'rich_text', 'headline', __( 'หัวข้อใหญ่ (ตัวหนา = ไล่สี)', 'tonkla' ) ),
				Field::make( 'textarea', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
				Field::make( 'checkbox', 'enable_domain_search', __( 'แสดงช่องค้นหาโดเมน', 'tonkla' ) ),
				Field::make( 'complex', 'buttons', __( 'ปุ่ม', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( '<%- text || "Button" %>' )
					->add_fields( $hero_buttons ),
				Field::make( 'separator', 'sep_hero_style', __( 'พื้นหลัง / สไตล์', 'tonkla' ) ),
				Field::make( 'select', 'bg_type', __( 'สไตล์พื้นหลัง', 'tonkla' ) )
					->add_options(
						array(
							'pattern' => __( 'ลายมืด (ค่าเริ่มต้น)', 'tonkla' ),
							'color'   => __( 'สีทึบ', 'tonkla' ),
							'image'   => __( 'รูปภาพ', 'tonkla' ),
						)
					)
					->set_default_value( 'pattern' ),
				tonkla_bg_color_field( 'dark' )
					->set_conditional_logic(
						array(
							array(
								'field' => 'bg_type',
								'value' => 'color',
							),
						)
					),
				Field::make( 'image', 'bg_image', __( 'รูปพื้นหลัง', 'tonkla' ) )
					->set_conditional_logic(
						array(
							array(
								'field' => 'bg_type',
								'value' => 'image',
							),
						)
					),
				Field::make( 'color', 'bg_overlay', __( 'สีซ้อนทับ', 'tonkla' ) )
					->set_alpha_enabled( true )
					->set_default_value( 'rgba(15, 23, 42, 0.9)' ),
			)
		)
	);
	$complex->set_header_template( $modules['hero_module']['header'] );

	// —— Service Grid ——
	$service_item_fields = array_merge(
		tonkla_icon_fields( array( 'class_key' => 'icon' ) ),
		array(
			Field::make( 'text', 'title', __( 'ชื่อการ์ด', 'tonkla' ) ),
			Field::make( 'textarea', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
			Field::make( 'complex', 'stack_icons', __( 'ไอคอน Tech Stack', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- title || icon_class || "Icon" %>' )
				->set_collapsed( true )
				->add_fields(
					array_merge(
						tonkla_icon_fields(
							array(
								'class_key'     => 'icon_class',
								'default_class' => 'fa-brands fa-php',
								'include_image' => true,
							)
						),
						array(
							Field::make( 'text', 'title', __( 'Tooltip', 'tonkla' ) ),
						)
					)
				),
		)
	);

	$complex->add_fields(
		'service_grid',
		$modules['service_grid']['label'],
		array_merge(
			array( tonkla_module_info_field( 'service_grid' ) ),
			array( Field::make( 'separator', 'sep_sg_content', __( 'เนื้อหา', 'tonkla' ) ) ),
			tonkla_section_header_fields(),
			array(
				Field::make( 'complex', 'items', __( 'การ์ด', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( tonkla_complex_item_title() )
					->add_fields( $service_item_fields ),
				Field::make( 'separator', 'sep_sg_layout', __( 'เลย์เอาต์', 'tonkla' ) ),
				Field::make( 'select', 'layout', __( 'จำนวนคอลัมน์', 'tonkla' ) )
					->add_options(
						array(
							'cols-3' => __( '3 คอลัมน์', 'tonkla' ),
							'cols-4' => __( '4 คอลัมน์', 'tonkla' ),
						)
					),
				Field::make( 'select', 'content_align', __( 'จัดแนวเนื้อหา', 'tonkla' ) )
					->add_options(
						array(
							'left'   => __( 'ซ้าย', 'tonkla' ),
							'center' => __( 'กลาง', 'tonkla' ),
							'right'  => __( 'ขวา', 'tonkla' ),
						)
					)
					->set_default_value( 'left' ),
				tonkla_bg_color_field( 'white' ),
			)
		)
	);
	$complex->set_header_template( $modules['service_grid']['header'] );

	// —— Tech Stack ——
	$tech_item_fields = array_merge(
		array(
			Field::make( 'text', 'title', __( 'ชื่อ', 'tonkla' ) ),
			Field::make( 'text', 'description', __( 'คำอธิบายสั้น', 'tonkla' ) ),
		),
		tonkla_icon_fields(
			array(
				'class_key'     => 'icon_class',
				'default_class' => 'fa-brands fa-laravel',
			)
		)
	);

	$complex->add_fields(
		'tech_stack_grid',
		$modules['tech_stack_grid']['label'],
		array_merge(
			array( tonkla_module_info_field( 'tech_stack_grid' ) ),
			array( Field::make( 'separator', 'sep_ts_content', __( 'เนื้อหา', 'tonkla' ) ) ),
			tonkla_section_header_fields(
				array(
					'title_default' => 'Tools & Technology We Master',
					'desc_rows'     => 4,
				)
			),
			array(
				Field::make( 'text', 'portfolio_link_text', __( 'ข้อความลิงก์', 'tonkla' ) )
					->set_default_value( 'ดูตัวอย่างผลงาน' ),
			),
			tonkla_link_fields(
				array(
					'type_key'    => 'portfolio_link_type',
					'url_key'     => 'portfolio_link',
					'content_key' => 'portfolio_link_content',
				)
			),
			array(
				Field::make( 'complex', 'items', __( 'รายการเทคโนโลยี', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( tonkla_complex_item_title() )
					->add_fields( $tech_item_fields ),
				Field::make( 'separator', 'sep_ts_style', __( 'สไตล์', 'tonkla' ) ),
				tonkla_bg_color_field( 'dark' ),
			)
		)
	);
	$complex->set_header_template( $modules['tech_stack_grid']['header'] );

	// —— Pricing ——
	$complex->add_fields(
		'pricing_table',
		$modules['pricing_table']['label'],
		array_merge(
			array( tonkla_module_info_field( 'pricing_table' ) ),
			array( Field::make( 'separator', 'sep_pr_content', __( 'เนื้อหา', 'tonkla' ) ) ),
			tonkla_section_header_fields( array( 'include_subtitle' => false ) ),
			array(
				Field::make( 'complex', 'plans', __( 'แพ็กเกจ', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( '<%- name || "Plan" %>' )
					->add_fields(
						array_merge(
							array(
								Field::make( 'text', 'name', __( 'ชื่อแพ็กเกจ', 'tonkla' ) ),
								Field::make( 'text', 'price', __( 'ราคา', 'tonkla' ) ),
								Field::make( 'text', 'period', __( 'รอบบิล', 'tonkla' ) ),
								Field::make( 'text', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
								Field::make( 'complex', 'features', __( 'รายการฟีเจอร์', 'tonkla' ) )
									->set_layout( 'tabbed-horizontal' )
									->set_header_template( '<%- text || "Feature" %>' )
									->set_collapsed( true )
									->add_fields(
										array(
											Field::make( 'text', 'text', __( 'ฟีเจอร์', 'tonkla' ) ),
										)
									),
								Field::make( 'text', 'button_text', __( 'ข้อความปุ่ม', 'tonkla' ) ),
							),
							tonkla_link_fields(
								array(
									'type_key'    => 'button_link_type',
									'url_key'     => 'button_link',
									'content_key' => 'button_link_content',
								)
							),
							array(
								Field::make( 'checkbox', 'recommended', __( 'แนะนำ (เน้นการ์ด)', 'tonkla' ) ),
							)
						)
					),
				Field::make( 'separator', 'sep_pr_style', __( 'สไตล์', 'tonkla' ) ),
				tonkla_bg_color_field( 'white' ),
			)
		)
	);
	$complex->set_header_template( $modules['pricing_table']['header'] );

	// —— Feature Split ——
	$feature_item_fields = array_merge(
		array( Field::make( 'separator', 'sep_general', __( 'ทั่วไป', 'tonkla' ) ) ),
		tonkla_icon_fields( array( 'class_key' => 'icon' ) ),
		array(
			Field::make( 'text', 'title', __( 'ชื่อ', 'tonkla' ) ),
			Field::make( 'text', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
			Field::make( 'separator', 'sep_visual', __( 'สื่อประกอบ', 'tonkla' ) ),
			Field::make( 'select', 'media_type', __( 'ประเภทสื่อ', 'tonkla' ) )
				->add_options(
					array(
						'code'  => __( 'หน้าต่างโค้ด', 'tonkla' ),
						'image' => __( 'รูปภาพ', 'tonkla' ),
					)
				)
				->set_default_value( 'code' ),
			Field::make( 'image', 'image', __( 'รูปภาพ', 'tonkla' ) )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'image',
						),
					)
				),
			Field::make( 'text', 'code_window_title', __( 'ชื่อหน้าต่าง', 'tonkla' ) )
				->set_default_value( 'automation-workflow.json' )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'code',
						),
					)
				),
			Field::make( 'rich_text', 'code_content', __( 'เนื้อหาโค้ด', 'tonkla' ) )
				->set_help_text( __( 'ใช้ HTML ได้ เช่น &lt;span class="text-blue-600"&gt;', 'tonkla' ) )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'code',
						),
					)
				),
			Field::make( 'text', 'code_button_text', __( 'ข้อความปุ่ม', 'tonkla' ) )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'code',
						),
					)
				),
		),
		tonkla_link_fields(
			array(
				'type_key'         => 'code_button_link_type',
				'url_key'          => 'code_button_link',
				'content_key'      => 'code_button_link_content',
				'extra_conditions' => array(
					array(
						'field' => 'media_type',
						'value' => 'code',
					),
				),
			)
		)
	);

	$complex->add_fields(
		'feature_split',
		$modules['feature_split']['label'],
		array_merge(
			array( tonkla_module_info_field( 'feature_split' ) ),
			array( Field::make( 'separator', 'sep_fs_content', __( 'เนื้อหา', 'tonkla' ) ) ),
			tonkla_section_header_fields(),
			array(
				Field::make( 'complex', 'items', __( 'รายการ', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( tonkla_complex_item_title() )
					->add_fields( $feature_item_fields ),
				Field::make( 'separator', 'sep_fs_layout', __( 'เลย์เอาต์', 'tonkla' ) ),
				Field::make( 'select', 'layout_columns', __( 'คอลัมน์', 'tonkla' ) )
					->add_options(
						array(
							'2' => __( '2 คอลัมน์ (แท็บโต้ตอบ)', 'tonkla' ),
							'1' => __( '1 คอลัมน์ (เรียงลง)', 'tonkla' ),
						)
					)
					->set_default_value( '2' ),
				tonkla_bg_color_field( 'white' ),
			)
		)
	);
	$complex->set_header_template( $modules['feature_split']['header'] );

	// —— Content Block ——
	$complex->add_fields(
		'content_block',
		$modules['content_block']['label'],
		array(
			tonkla_module_info_field( 'content_block' ),
			Field::make( 'separator', 'sep_cb_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'rich_text', 'content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'select', 'media_type', __( 'ประเภทสื่อ', 'tonkla' ) )
				->add_options(
					array(
						'image' => __( 'รูปภาพ', 'tonkla' ),
						'code'  => __( 'โค้ด / HTML', 'tonkla' ),
					)
				),
			Field::make( 'image', 'image', __( 'รูปภาพ', 'tonkla' ) )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'image',
						),
					)
				),
			Field::make( 'rich_text', 'code_content', __( 'โค้ด / HTML', 'tonkla' ) )
				->set_conditional_logic(
					array(
						array(
							'field' => 'media_type',
							'value' => 'code',
						),
					)
				),
			Field::make( 'separator', 'sep_cb_layout', __( 'เลย์เอาต์', 'tonkla' ) ),
			Field::make( 'select', 'layout_align', __( 'จัดแนว', 'tonkla' ) )
				->add_options(
					array(
						'left'   => __( 'สื่อซ้าย', 'tonkla' ),
						'right'  => __( 'สื่อขวา', 'tonkla' ),
						'center' => __( 'ข้อความกึ่งกลาง', 'tonkla' ),
					)
				),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['content_block']['header'] );

	// —— CTA ——
	$cta_button_fields = array_merge(
		array( Field::make( 'text', 'text', __( 'ข้อความปุ่ม', 'tonkla' ) ) ),
		tonkla_link_fields(),
		array(
			Field::make( 'select', 'style', __( 'สไตล์', 'tonkla' ) )
				->add_options(
					array(
						'primary'   => __( 'หลัก (Brand)', 'tonkla' ),
						'secondary' => __( 'รอง', 'tonkla' ),
					)
				),
		)
	);

	$complex->add_fields(
		'cta_module',
		$modules['cta_module']['label'],
		array(
			tonkla_module_info_field( 'cta_module' ),
			Field::make( 'separator', 'sep_cta_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'text', 'title', __( 'หัวข้อ', 'tonkla' ) ),
			Field::make( 'textarea', 'text', __( 'ข้อความ', 'tonkla' ) ),
			Field::make( 'complex', 'buttons', __( 'ปุ่ม', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- text || "Button" %>' )
				->add_fields( $cta_button_fields ),
			Field::make( 'separator', 'sep_cta_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'brand' ),
		)
	);
	$complex->set_header_template( $modules['cta_module']['header'] );

	// —— Workflow ——
	$workflow_node_fields = array_merge(
		tonkla_icon_fields(
			array(
				'class_key'     => 'icon',
				'color_key'     => 'color_class',
				'color_label'   => __( 'สีขอบ', 'tonkla' ),
				'include_image' => true,
			)
		),
		array(
			Field::make( 'text', 'title', __( 'ชื่อโหนด', 'tonkla' ) ),
			Field::make( 'text', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
		)
	);

	$complex->add_fields(
		'workflow_module',
		$modules['workflow_module']['label'],
		array(
			tonkla_module_info_field( 'workflow_module' ),
			Field::make( 'separator', 'sep_wf_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'text', 'headline', __( 'หัวข้อ', 'tonkla' ) ),
			Field::make( 'complex', 'nodes', __( 'โหนด', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- title || "Node" %>' )
				->add_fields( $workflow_node_fields ),
			Field::make( 'separator', 'sep_wf_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['workflow_module']['header'] );

	// —— Stats ——
	$stats_item_fields = array_merge(
		tonkla_icon_fields(
			array(
				'class_key'     => 'icon',
				'default_class' => 'fa-solid fa-server',
			)
		),
		array(
			Field::make( 'text', 'stat_value', __( 'ค่า (เช่น 99.9%)', 'tonkla' ) ),
			Field::make( 'text', 'label', __( 'ป้ายกำกับ', 'tonkla' ) ),
		)
	);

	$complex->add_fields(
		'stats_module',
		$modules['stats_module']['label'],
		array(
			tonkla_module_info_field( 'stats_module' ),
			Field::make( 'separator', 'sep_st_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'complex', 'items', __( 'รายการสถิติ', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- label || stat_value || "Stat" %>' )
				->add_fields( $stats_item_fields ),
			Field::make( 'separator', 'sep_st_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['stats_module']['header'] );

	// —— Process ——
	$complex->add_fields(
		'process_module',
		$modules['process_module']['label'],
		array(
			tonkla_module_info_field( 'process_module' ),
			Field::make( 'separator', 'sep_pc_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'text', 'section_title', __( 'หัวข้อส่วน', 'tonkla' ) )
				->set_default_value( 'ขั้นตอนการทำงานของเรา' ),
			Field::make( 'complex', 'steps', __( 'ขั้นตอน', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- title || "Step " + (Number(_index) + 1) %>' )
				->set_help_text( __( 'แนะนำ 3–5 ขั้นตอน', 'tonkla' ) )
				->add_fields(
					array(
						Field::make( 'text', 'title', __( 'ชื่อขั้นตอน', 'tonkla' ) ),
						Field::make( 'select', 'icon_color', __( 'สีขั้นตอน', 'tonkla' ) )
							->add_options( tonkla_icon_colors() )
							->set_classes( 'tonkla-color-select' )
							->set_default_value( 'brand' ),
						Field::make( 'textarea', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
					)
				),
			Field::make( 'separator', 'sep_pc_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['process_module']['header'] );

	// —— FAQ ——
	$complex->add_fields(
		'faq_module',
		$modules['faq_module']['label'],
		array(
			tonkla_module_info_field( 'faq_module' ),
			Field::make( 'separator', 'sep_faq_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'text', 'section_title', __( 'หัวข้อส่วน', 'tonkla' ) )
				->set_default_value( 'คำถามที่พบบ่อย (FAQ)' ),
			Field::make( 'complex', 'items', __( 'คำถาม', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- question || "Question" %>' )
				->add_fields(
					array(
						Field::make( 'text', 'question', __( 'คำถาม', 'tonkla' ) ),
						Field::make( 'rich_text', 'answer', __( 'คำตอบ', 'tonkla' ) ),
					)
				),
			Field::make( 'separator', 'sep_faq_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['faq_module']['header'] );

	// —— Testimonials ——
	$complex->add_fields(
		'testimonials_module',
		$modules['testimonials_module']['label'],
		array(
			tonkla_module_info_field( 'testimonials_module' ),
			Field::make( 'separator', 'sep_tm_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'text', 'section_title', __( 'หัวข้อส่วน', 'tonkla' ) )
				->set_default_value( 'เสียงจากลูกค้าของเรา' ),
			Field::make( 'complex', 'items', __( 'รีวิว', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- name || "Customer" %>' )
				->add_fields(
					array(
						Field::make( 'textarea', 'quote', __( 'ข้อความรีวิว', 'tonkla' ) ),
						Field::make( 'text', 'name', __( 'ชื่อ', 'tonkla' ) ),
						Field::make( 'text', 'role', __( 'ตำแหน่ง / บริษัท', 'tonkla' ) ),
						Field::make( 'image', 'image', __( 'รูปโปรไฟล์ (ไม่บังคับ)', 'tonkla' ) )
							->set_value_type( 'url' ),
					)
				),
			Field::make( 'separator', 'sep_tm_style', __( 'สไตล์', 'tonkla' ) ),
			tonkla_bg_color_field( 'white' ),
		)
	);
	$complex->set_header_template( $modules['testimonials_module']['header'] );

	// —— Contact ——
	$contact_social_fields = array_merge(
		tonkla_icon_fields(
			array(
				'class_key'     => 'icon_class',
				'default_class' => 'fa-brands fa-facebook-f',
				'include_color' => false,
			)
		),
		array(
			Field::make( 'text', 'link', __( 'URL', 'tonkla' ) )
				->set_attribute( 'placeholder', 'https://' ),
		)
	);

	$complex->add_fields(
		'contact_section',
		$modules['contact_section']['label'],
		array(
			tonkla_module_info_field( 'contact_section' ),
			Field::make( 'separator', 'sep_ct_form', __( 'ฟอร์ม', 'tonkla' ) ),
			Field::make( 'text', 'form_shortcode', __( 'CF7 Shortcode', 'tonkla' ) )
				->set_help_text( __( 'เช่น [contact-form-7 id="123"]', 'tonkla' ) ),
			Field::make( 'text', 'info_title', __( 'หัวข้อข้อมูลติดต่อ', 'tonkla' ) )
				->set_default_value( 'ข้อมูลการติดต่อ' ),
			Field::make( 'separator', 'sep_address', __( 'ที่อยู่', 'tonkla' ) ),
			Field::make( 'text', 'address_title', __( 'หัวข้อที่อยู่', 'tonkla' ) )
				->set_default_value( 'ที่อยู่บริษัท' ),
			Field::make( 'textarea', 'address', __( 'รายละเอียดที่อยู่', 'tonkla' ) )->set_rows( 4 ),
			Field::make( 'separator', 'sep_phone', __( 'โทรศัพท์', 'tonkla' ) ),
			Field::make( 'text', 'phone_title', __( 'หัวข้อโทรศัพท์', 'tonkla' ) )
				->set_default_value( 'เบอร์โทรศัพท์' ),
			Field::make( 'text', 'phone_office', __( 'เบอร์ออฟฟิศ', 'tonkla' ) ),
			Field::make( 'text', 'phone_hotline', __( 'สายด่วน', 'tonkla' ) ),
			Field::make( 'separator', 'sep_email', __( 'อีเมล', 'tonkla' ) ),
			Field::make( 'text', 'email_title', __( 'หัวข้ออีเมล', 'tonkla' ) )
				->set_default_value( 'อีเมล' ),
			Field::make( 'complex', 'emails', __( 'รายการอีเมล', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- email || "Email" %>' )
				->add_fields(
					array(
						Field::make( 'text', 'email', __( 'อีเมล', 'tonkla' ) ),
					)
				),
			Field::make( 'separator', 'sep_line', __( 'LINE', 'tonkla' ) ),
			Field::make( 'text', 'line_title', __( 'หัวข้อ LINE', 'tonkla' ) )
				->set_default_value( 'LINE Official' ),
			Field::make( 'text', 'line_id', __( 'LINE ID', 'tonkla' ) ),
			Field::make( 'separator', 'sep_socials', __( 'โซเชียล', 'tonkla' ) ),
			Field::make( 'complex', 'socials', __( 'ลิงก์โซเชียล', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- icon_class || link || "Social" %>' )
				->add_fields( $contact_social_fields ),
		)
	);
	$complex->set_header_template( $modules['contact_section']['header'] );

	// —— Map & FAQ ——
	$complex->add_fields(
		'map_faq_section',
		$modules['map_faq_section']['label'],
		array(
			tonkla_module_info_field( 'map_faq_section' ),
			Field::make( 'separator', 'sep_mf_content', __( 'เนื้อหา', 'tonkla' ) ),
			Field::make( 'textarea', 'map_embed_code', __( 'โค้ดฝัง Google Map', 'tonkla' ) )
				->set_rows( 4 )
				->set_help_text( __( 'วางโค้ด iframe จาก Google Maps', 'tonkla' ) ),
			Field::make( 'text', 'faq_title', __( 'หัวข้อ FAQ', 'tonkla' ) )
				->set_default_value( 'คำถามที่พบบ่อย (FAQ)' ),
			Field::make( 'complex', 'faq_items', __( 'รายการ FAQ', 'tonkla' ) )
				->set_layout( 'tabbed-horizontal' )
				->set_header_template( '<%- question || "Question" %>' )
				->add_fields(
					array(
						Field::make( 'text', 'question', __( 'คำถาม', 'tonkla' ) ),
						Field::make( 'rich_text', 'answer', __( 'คำตอบ', 'tonkla' ) ),
					)
				),
		)
	);
	$complex->set_header_template( $modules['map_faq_section']['header'] );

	// —— Portfolio ——
	$portfolio_item_fields = array_merge(
		array(
			Field::make( 'image', 'image', __( 'รูปปกผลงาน', 'tonkla' ) )
				->set_help_text( __( 'แนะนำอัตราส่วน 16:10 หรือ 4:3', 'tonkla' ) ),
			Field::make( 'text', 'title', __( 'ชื่อผลงาน', 'tonkla' ) ),
			Field::make( 'text', 'category', __( 'หมวดหมู่', 'tonkla' ) )
				->set_help_text( __( 'เช่น Web App, ERP, Mobile', 'tonkla' ) )
				->set_attribute( 'placeholder', 'Web Application' ),
			Field::make( 'text', 'client', __( 'ลูกค้า / องค์กร', 'tonkla' ) )
				->set_attribute( 'placeholder', __( 'ไม่บังคับ', 'tonkla' ) ),
			Field::make( 'text', 'year', __( 'ปี', 'tonkla' ) )
				->set_attribute( 'placeholder', '2025' ),
			Field::make( 'textarea', 'description', __( 'คำอธิบายสั้น', 'tonkla' ) )
				->set_rows( 3 )
				->set_help_text( __( 'สรุป 1–2 ประโยคว่าทำอะไรให้ใคร', 'tonkla' ) ),
			Field::make( 'text', 'link_text', __( 'ข้อความลิงก์', 'tonkla' ) )
				->set_default_value( __( 'ดูรายละเอียด', 'tonkla' ) ),
		),
		tonkla_link_fields()
	);

	$complex->add_fields(
		'portfolio_module',
		$modules['portfolio_module']['label'],
		array_merge(
			array( tonkla_module_info_field( 'portfolio_module' ) ),
			array( Field::make( 'separator', 'sep_pf_content', __( 'เนื้อหา', 'tonkla' ) ) ),
			tonkla_section_header_fields(
				array(
					'title_default' => __( 'ผลงานของเรา', 'tonkla' ),
				)
			),
			array(
				Field::make( 'complex', 'items', __( 'รายการผลงาน', 'tonkla' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_header_template( '<%- title || category || "Project" %>' )
					->add_fields( $portfolio_item_fields ),
				Field::make( 'separator', 'sep_pf_layout', __( 'เลย์เอาต์', 'tonkla' ) ),
				Field::make( 'select', 'layout', __( 'จำนวนคอลัมน์', 'tonkla' ) )
					->add_options(
						array(
							'cols-2' => __( '2 คอลัมน์', 'tonkla' ),
							'cols-3' => __( '3 คอลัมน์', 'tonkla' ),
						)
					)
					->set_default_value( 'cols-3' ),
				tonkla_bg_color_field( 'white' ),
				Field::make( 'separator', 'sep_pf_cta', __( 'ลิงก์ดูทั้งหมด (ไม่บังคับ)', 'tonkla' ) ),
				Field::make( 'text', 'view_all_text', __( 'ข้อความปุ่ม', 'tonkla' ) )
					->set_default_value( __( 'ดูผลงานทั้งหมด', 'tonkla' ) ),
			),
			tonkla_link_fields(
				array(
					'type_key'    => 'view_all_link_type',
					'url_key'     => 'view_all_link',
					'content_key' => 'view_all_link_content',
				)
			)
		)
	);
	$complex->set_header_template( $modules['portfolio_module']['header'] );

	Container::make( 'post_meta', __( 'Page Builder by Tonkla IT', 'tonkla' ) )
		->where( 'post_template', '=', 'page-flexible.php' )
		->add_fields( array( $complex ) );
}
