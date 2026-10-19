<?php
/**
 * Carbon Fields: theme options, menu meta, page builder, term meta.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

require_once get_template_directory() . '/inc/admin-dashboard.php';

/**
 * Page choices for PDPA privacy policy (single select).
 *
 * @return array<string,string>
 */
function tonkla_pdpa_policy_page_options(): array {
	$options = array(
		'0' => __( '— เลือกหน้า —', 'tonkla' ),
	);

	$pages = get_pages(
		array(
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
			'number'      => 200,
		)
	);

	if ( ! is_array( $pages ) ) {
		return $options;
	}

	foreach ( $pages as $page ) {
		$options[ (string) (int) $page->ID ] = $page->post_title;
	}

	return $options;
}

/**
 * Resolved privacy-policy page ID (new select, or legacy association).
 *
 * @return int
 */
function tonkla_get_pdpa_policy_page_id(): int {
	if ( ! function_exists( 'carbon_get_theme_option' ) ) {
		return 0;
	}

	$id = absint( carbon_get_theme_option( 'pdpa_policy_page_id' ) );
	if ( $id > 0 ) {
		return $id;
	}

	// Legacy association field (array of { id, type, subtype }).
	$assoc = carbon_get_theme_option( 'pdpa_policy_page' );
	if ( is_array( $assoc ) && ! empty( $assoc[0]['id'] ) ) {
		return absint( $assoc[0]['id'] );
	}

	return 0;
}

/**
 * Permalink for the PDPA privacy policy page (empty if unset).
 *
 * @return string
 */
function tonkla_get_pdpa_policy_url(): string {
	$id = tonkla_get_pdpa_policy_page_id();
	if ( $id > 0 ) {
		$url = get_permalink( $id );
		return is_string( $url ) ? $url : '';
	}

	$fallback = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
	return is_string( $fallback ) ? $fallback : '';
}

/**
 * Wireframe gallery HTML for PDPA bar position (admin preview).
 *
 * @return string
 */
function tonkla_pdpa_position_wireframes_html(): string {
	$items = array(
		'bottom-left'  => __( 'ลอยมุมล่างซ้าย', 'tonkla' ),
		'bottom-right' => __( 'ลอยมุมล่างขวา', 'tonkla' ),
		'bottom-bar'   => __( 'แถบเต็มด้านล่าง', 'tonkla' ),
		'center'       => __( 'กล่องกลางจอ', 'tonkla' ),
	);

	$html  = '<div class="tonkla-pdpa-wireframes" role="list" aria-label="' . esc_attr__( 'ตัวอย่างตำแหน่งแถบคุกกี้', 'tonkla' ) . '">';
	$html .= '<p class="tonkla-pdpa-wireframes__hint">' . esc_html__( 'คลิกตัวอย่างเพื่อเลือกตำแหน่ง — กล่องเข้มคือแถบยินยอม', 'tonkla' ) . '</p>';
	$html .= '<div class="tonkla-pdpa-wireframes__grid">';

	foreach ( $items as $slug => $label ) {
		$html .= sprintf(
			'<button type="button" class="tonkla-pdpa-wireframe" data-position="%1$s" role="listitem" aria-pressed="false">'
			. '<span class="tonkla-pdpa-wireframe__screen" aria-hidden="true">'
			. '<span class="tonkla-pdpa-wireframe__chrome"></span>'
			. '<span class="tonkla-pdpa-wireframe__lines"></span>'
			. '<span class="tonkla-pdpa-wireframe__bar tonkla-pdpa-wireframe__bar--%1$s"></span>'
			. '</span>'
			. '<span class="tonkla-pdpa-wireframe__label">%2$s</span>'
			. '</button>',
			esc_attr( $slug ),
			esc_html( $label )
		);
	}

	$html .= '</div></div>';
	return $html;
}

/**
 * One-time: copy legacy association → select so existing sites keep their page.
 */
function tonkla_migrate_pdpa_policy_page_field(): void {
	if ( get_option( 'tonkla_pdpa_policy_page_migrated' ) ) {
        return;
    }
	if ( ! function_exists( 'carbon_get_theme_option' ) || ! function_exists( 'carbon_set_theme_option' ) ) {
		return;
	}

	$existing = absint( carbon_get_theme_option( 'pdpa_policy_page_id' ) );
	if ( $existing > 0 ) {
		update_option( 'tonkla_pdpa_policy_page_migrated', '1', false );
		return;
	}

	$assoc = carbon_get_theme_option( 'pdpa_policy_page' );
	if ( is_array( $assoc ) && ! empty( $assoc[0]['id'] ) ) {
		carbon_set_theme_option( 'pdpa_policy_page_id', (string) absint( $assoc[0]['id'] ) );
	}

	update_option( 'tonkla_pdpa_policy_page_migrated', '1', false );
}
add_action( 'carbon_fields_fields_registered', 'tonkla_migrate_pdpa_policy_page_field' );

/**
 * Icon color options for menu / builder fields.
 *
 * @return array<string,string>
 */
function tonkla_icon_colors() {
	return array(
		'brand'   => 'Brand Green',
		'slate'   => 'Slate',
		'red'     => 'Red',
		'orange'  => 'Orange',
		'amber'   => 'Amber',
		'yellow'  => 'Yellow',
		'lime'    => 'Lime',
		'green'   => 'Green',
		'emerald' => 'Emerald',
		'teal'    => 'Teal',
		'cyan'    => 'Cyan',
		'sky'     => 'Sky Blue',
		'blue'    => 'Blue',
		'indigo'  => 'Indigo',
		'violet'  => 'Violet',
		'purple'  => 'Purple',
		'fuchsia' => 'Fuchsia',
		'pink'    => 'Pink',
		'rose'    => 'Rose',
	);
}

/**
 * Shared background color options for page builder modules.
 *
 * @return array<string,string>
 */
function tonkla_bg_color_options() {
	return array(
		'white'      => __( 'ขาว', 'tonkla' ),
		'light'      => __( 'อ่อน', 'tonkla' ),
		'light-gray' => __( 'เทาอ่อน', 'tonkla' ),
		'gray'       => __( 'เทา', 'tonkla' ),
		'dark-gray'  => __( 'เทาเข้ม', 'tonkla' ),
		'dark'       => __( 'เข้ม', 'tonkla' ),
		'brand'      => __( 'สีแบรนด์', 'tonkla' ),
	);
}

/**
 * Button link type options (custom vs post).
 *
 * @return array<string,string>
 */
function tonkla_button_link_type_options() {
	return array(
		'custom' => __( 'URL กำหนดเอง', 'tonkla' ),
		'post'   => __( 'หน้า / โพสต์', 'tonkla' ),
	);
}

/**
 * Shared complex field item title template.
 *
 * @return string
 */
function tonkla_complex_item_title() {
	return '<%- title || "Item" %>';
}

/**
 * Carbon fields for one typography role block (Appearance tab).
 *
 * @param string $role  Role slug: body|heading|nav|caption.
 * @param string $label Block title.
 * @return array<int,mixed>
 */
function tonkla_carbon_appearance_role_fields( string $role, string $label ): array {
	$defaults = tonkla_typography_role_defaults()[ $role ];
	$fields   = array(
		Field::make( 'separator', "appearance_{$role}_sep", $label ),
		Field::make( 'select', "appearance_{$role}_font", __( 'ฟอนต์', 'tonkla' ) )
			->add_options( tonkla_font_options() )
			->set_default_value( $defaults['font'] )
			->set_width( 50 ),
	);

	if ( 'heading' === $role ) {
		$fields[] = Field::make( 'select', "appearance_{$role}_size", __( 'สเกลขนาดหัวข้อ', 'tonkla' ) )
			->add_options( tonkla_heading_scale_options() )
			->set_default_value( $defaults['size'] )
			->set_width( 25 );
	} else {
		$fields[] = Field::make( 'select', "appearance_{$role}_size", __( 'ขนาด', 'tonkla' ) )
			->add_options( tonkla_font_size_options() )
			->set_default_value( $defaults['size'] )
			->set_width( 25 );
	}

	$fields[] = Field::make( 'select', "appearance_{$role}_weight", __( 'น้ำหนัก', 'tonkla' ) )
		->add_options( tonkla_font_weight_options() )
		->set_default_value( $defaults['weight'] )
		->set_width( 25 );

	$fields[] = Field::make( 'color', "appearance_{$role}_color", __( 'สีตัวอักษร', 'tonkla' ) )
		->set_palette( array( '#0f172a', '#1e293b', '#334155', '#475569', '#64748b', '#ffffff' ) )
		->set_help_text( __( 'ว่างไว้ = ใช้สีพื้นผิว/ค่าเริ่มต้นของธีม', 'tonkla' ) )
		->set_width( 50 );

	return $fields;
}

/**
 * Language switcher field with Polylang status help text.
 *
 * @return \Carbon_Fields\Field\Field
 */
function tonkla_language_switcher_field() {
	$field     = Field::make( 'checkbox', 'enable_language_switcher', __( 'เปิดตัวสลับภาษา', 'tonkla' ) );
	$pll_active = function_exists( 'pll_the_languages' );
	$lang_count = $pll_active ? count( pll_languages_list() ) : 0;

	if ( ! $pll_active ) {
		$field->set_classes( 'carbon-disabled-field tonkla-field-status--bad' );
		$field->set_help_text( __( 'ยังไม่ได้เปิดใช้ปลั๊กอิน Polylang — ตัวสลับภาษาจะถูกปิด', 'tonkla' ) );
		$field->set_default_value( false );
	} elseif ( $lang_count < 2 ) {
		$field->set_classes( 'carbon-disabled-field tonkla-field-status--warn' );
		$field->set_help_text( __( 'ตั้งค่าน้อยกว่า 2 ภาษา — ตัวสลับภาษาจะถูกซ่อน', 'tonkla' ) );
		$field->set_default_value( false );
	} else {
		$field->set_classes( 'tonkla-field-status--ok' );
		$field->set_help_text( __( 'ตรวจพบ Polylang แล้ว ตัวสลับภาษาจะทำงานอัตโนมัติ', 'tonkla' ) );
	}

	return $field;
}

/**
 * Overview (Dashboard) — keep stable parent slug.
 */
Container::make( 'theme_options', __( 'Dashboard', 'tonkla' ) )
	->set_page_menu_title( 'TONKLA - IT' )
	->set_page_file( TONKLA_ADMIN_SLUG_PARENT )
	->set_icon( 'dashicons-admin-site-alt3' )
	->add_fields(
                        array(
			Field::make( 'html', 'dashboard_stats' )
				->set_html(
					static function () {
						return tonkla_render_admin_dashboard();
					}
				),
		)
	);

/**
 * Site settings — Header / Footer / Social / TOC.
 */
Container::make( 'theme_options', __( 'ตั้งค่าเว็บไซต์', 'tonkla' ) )
	->set_page_parent( TONKLA_ADMIN_SLUG_PARENT )
	->set_page_file( TONKLA_ADMIN_SLUG_SITE )
	->add_tab(
		__( 'ส่วนหัว', 'tonkla' ),
                                array(
			Field::make( 'image', 'main_logo', __( 'โลโก้หลัก', 'tonkla' ) )
				->set_width( 50 ),
			Field::make( 'image', 'footer_logo', __( 'โลโก้ส่วนท้าย', 'tonkla' ) )
				->set_width( 50 )
				->set_help_text( __( 'ถ้าไม่ตั้ง จะใช้โลโก้หลักแทน', 'tonkla' ) ),
			Field::make( 'checkbox', 'enable_client_login', __( 'แสดงปุ่มเข้าสู่ระบบลูกค้า', 'tonkla' ) )
				->set_default_value( true )
				->set_width( 50 ),
			Field::make( 'text', 'login_url', __( 'URL เข้าสู่ระบบลูกค้า', 'tonkla' ) )
				->set_attribute( 'placeholder', 'https://portal.tonkla.co.th' )
				->set_width( 50 )
				->set_conditional_logic(
					array(
						array(
							'field' => 'enable_client_login',
							'value' => true,
						),
					)
				),
			tonkla_language_switcher_field(),
			Field::make( 'checkbox', 'show_language_flag', __( 'แสดงธงภาษา', 'tonkla' ) )
				->set_default_value( true )
				->set_help_text( __( 'แสดงธงประเทศในตัวสลับภาษา', 'tonkla' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'enable_language_switcher',
							'value' => true,
						),
					)
				),
		)
	)
	->add_tab(
		__( 'ส่วนท้าย', 'tonkla' ),
                                array(
			Field::make( 'textarea', 'footer_description', __( 'คำอธิบายส่วนท้าย', 'tonkla' ) )
				->set_rows( 3 )
				->set_help_text( __( 'ว่างไว้จะใช้ “คำโปรยเว็บไซต์” จาก Settings → General (ถ้าไม่มีคำโปรย จะใช้ชื่อเว็บไซต์)', 'tonkla' ) ),
			Field::make( 'text', 'copyright_text', __( 'ข้อความลิขสิทธิ์', 'tonkla' ) )
				->set_help_text( __( 'ว่างไว้จะแสดง © ปี + ชื่อเว็บไซต์จาก Settings → General', 'tonkla' ) )
				->set_attribute( 'placeholder', '© 2026 Your Site Name. All rights reserved.' ),
			Field::make( 'checkbox', 'footer_show_credit', __( 'แสดงเครดิตผู้พัฒนาธีม', 'tonkla' ) )
				->set_default_value( true )
				->set_help_text( __( 'แสดงข้อความ “พัฒนาโดย ต้นกล้า ไอที” ต่อท้ายข้อความลิขสิทธิ์ — เอาเครื่องหมายออกเพื่อซ่อน', 'tonkla' ) ),
			Field::make( 'checkbox', 'enable_back_to_top', __( 'ปุ่มกลับขึ้นบน', 'tonkla' ) )
				->set_default_value( true ),
		)
	)
	->add_tab(
		__( 'ติดต่อและโซเชียล', 'tonkla' ),
                                array(
			Field::make( 'text', 'phone_number', __( 'เบอร์โทรศัพท์', 'tonkla' ) )
				->set_width( 50 ),
			Field::make( 'text', 'line_url', __( 'URL LINE', 'tonkla' ) )
				->set_width( 50 ),
			Field::make( 'text', 'facebook_url', __( 'URL Facebook', 'tonkla' ) )
				->set_width( 50 ),
			Field::make( 'text', 'twitter_url', __( 'URL X (Twitter)', 'tonkla' ) )
				->set_width( 50 ),
			Field::make( 'text', 'instagram_url', __( 'URL Instagram', 'tonkla' ) )
				->set_width( 50 ),
		)
	)
	->add_tab(
		__( 'รูปลักษณ์', 'tonkla' ),
		array_merge(
                                array(
				Field::make( 'separator', 'appearance_brand_sep', __( 'สีแบรนด์', 'tonkla' ) ),
				Field::make( 'html', 'appearance_swatches' )
					->set_html(
						static function () {
							return tonkla_color_preset_swatches_html();
						}
					),
				Field::make( 'select', 'appearance_color_preset', __( 'พรีเซ็ตสีหลัก', 'tonkla' ) )
					->add_options( tonkla_color_preset_options() )
					->set_default_value( 'emerald' )
					->set_help_text( __( 'เปลี่ยนสีปุ่ม ลิงก์ และจุดเน้นทั้งเว็บ — ค่าเริ่มต้นคือเขียวมรกต', 'tonkla' ) ),
				Field::make( 'color', 'appearance_custom_brand', __( 'สีหลักกำหนดเอง', 'tonkla' ) )
					->set_palette( array( '#10b981', '#0ea5e9', '#6366f1', '#f43f5e', '#f59e0b', '#475569' ) )
					->set_help_text( __( 'ใช้เมื่อเลือกพรีเซ็ต “กำหนดเอง” — ระบบจะคำนวณสีอ่อน/เข้มให้อัตโนมัติ', 'tonkla' ) )
					->set_conditional_logic(
						array(
							array(
								'field' => 'appearance_color_preset',
								'value' => 'custom',
							),
						)
					),
				Field::make( 'separator', 'appearance_surface_sep', __( 'สีพื้นผิว', 'tonkla' ) ),
				Field::make( 'color', 'appearance_surface_bg', __( 'พื้นหลังหน้า', 'tonkla' ) )
					->set_palette( array( '#f8fafc', '#ffffff', '#f1f5f9', '#0f172a' ) )
					->set_help_text( __( 'ว่างไว้ = slate-50', 'tonkla' ) )
					->set_width( 25 ),
				Field::make( 'color', 'appearance_surface_text', __( 'ข้อความหลัก', 'tonkla' ) )
					->set_palette( array( '#0f172a', '#1e293b', '#334155' ) )
					->set_help_text( __( 'ว่างไว้ = slate-800', 'tonkla' ) )
					->set_width( 25 ),
				Field::make( 'color', 'appearance_surface_muted', __( 'ข้อความรอง', 'tonkla' ) )
					->set_palette( array( '#64748b', '#94a3b8', '#475569' ) )
					->set_help_text( __( 'ว่างไว้ = slate-500', 'tonkla' ) )
					->set_width( 25 ),
				Field::make( 'color', 'appearance_surface_link', __( 'สีลิงก์', 'tonkla' ) )
					->set_palette( array( '#047857', '#0ea5e9', '#1e293b' ) )
					->set_help_text( __( 'ว่างไว้ = สี brand เข้ม', 'tonkla' ) )
					->set_width( 25 ),
				Field::make( 'html', 'appearance_font_limit_note' )
					->set_html(
						'<p style="margin:0 0 8px;color:#64748b;font-size:13px;">'
						. esc_html__( 'ตัวอักษรตามบทบาท — Google Fonts โหลดสูงสุด 2 ตระกูล (ลำดับ: เนื้อหา → หัวข้อ → เมนู → คำบรรยาย); ตระกูลที่เกินจะใช้ฟอนต์เนื้อหาแทน', 'tonkla' )
						. '</p>'
					),
			),
			tonkla_carbon_appearance_role_fields( 'body', __( 'เนื้อหา (Body)', 'tonkla' ) ),
			tonkla_carbon_appearance_role_fields( 'heading', __( 'หัวข้อ (Heading)', 'tonkla' ) ),
			tonkla_carbon_appearance_role_fields( 'nav', __( 'เมนูนำทาง (Nav)', 'tonkla' ) ),
			tonkla_carbon_appearance_role_fields( 'caption', __( 'คำบรรยาย / Meta (Caption)', 'tonkla' ) ),
                                array(
				Field::make( 'separator', 'appearance_button_sep', __( 'ปุ่ม', 'tonkla' ) ),
				Field::make( 'select', 'appearance_button_radius', __( 'ความโค้งมุมปุ่ม', 'tonkla' ) )
					->add_options(
						array(
							'0'    => __( 'เหลี่ยม (0)', 'tonkla' ),
							'4'    => '4px',
							'8'    => '8px (' . __( 'ค่าเริ่มต้น', 'tonkla' ) . ')',
							'12'   => '12px',
							'9999' => __( 'แคปซูล', 'tonkla' ),
						)
					)
					->set_default_value( '8' )
					->set_width( 50 ),
			)
		)
	)
	->add_tab(
		__( 'สารบัญบทความ', 'tonkla' ),
                                array(
			Field::make( 'checkbox', 'toc_enable', __( 'เปิดสารบัญบทความ', 'tonkla' ) )
				->set_default_value( true ),
			Field::make( 'text', 'toc_title', __( 'หัวข้อสารบัญ', 'tonkla' ) )
				->set_default_value( 'สารบัญ' )
				->set_conditional_logic(
					array(
						array(
							'field' => 'toc_enable',
							'value' => true,
						),
					)
				),
			Field::make( 'multiselect', 'toc_depth', __( 'ระดับหัวข้อ', 'tonkla' ) )
				->add_options(
                                array(
						'h2' => 'H2',
						'h3' => 'H3',
						'h4' => 'H4',
						'h5' => 'H5',
						'h6' => 'H6',
					)
				)
				->set_default_value( array( 'h2', 'h3', 'h4' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'toc_enable',
							'value' => true,
						),
					)
				),
		)
	);

/**
 * Marketing & privacy — PDPA / Ads / Chat / Scripts.
 */
Container::make( 'theme_options', __( 'การตลาดและความเป็นส่วนตัว', 'tonkla' ) )
	->set_page_parent( TONKLA_ADMIN_SLUG_PARENT )
	->set_page_file( TONKLA_ADMIN_SLUG_MARKETING )
	->add_tab(
		__( 'คุกกี้ PDPA', 'tonkla' ),
                                array(
			Field::make( 'checkbox', 'pdpa_enable', __( 'แสดงแถบยินยอมคุกกี้บนหน้าบ้าน', 'tonkla' ) )
				->set_default_value( true )
				->set_help_text( __( 'ติ๊กเพื่อแสดงบนหน้าบ้าน — ตั้งค่าด้านล่างได้เสมอแม้ปิดการแสดง', 'tonkla' ) ),
			Field::make( 'select', 'pdpa_position', __( 'ตำแหน่งแถบ', 'tonkla' ) )
				->add_options(
					array(
						'bottom-left'  => __( 'ลอยมุมล่างซ้าย (ค่าเริ่มต้น)', 'tonkla' ),
						'bottom-right' => __( 'ลอยมุมล่างขวา', 'tonkla' ),
						'bottom-bar'   => __( 'แถบเต็มความกว้างด้านล่าง', 'tonkla' ),
						'center'       => __( 'กล่องกลางจอ', 'tonkla' ),
					)
				)
				->set_default_value( 'bottom-left' )
				->set_classes( 'tonkla-pdpa-position-field' ),
			Field::make( 'html', 'pdpa_position_wireframes' )
				->set_html(
					static function () {
						return tonkla_pdpa_position_wireframes_html();
					}
				),
			Field::make( 'rich_text', 'pdpa_text', __( 'ข้อความยินยอม', 'tonkla' ) )
				->set_default_value( 'เราใช้คุกกี้เพื่อเพิ่มประสิทธิภาพและประสบการณ์ที่ดีในการใช้งานเว็บไซต์ คุณสามารถศึกษารายละเอียดได้ที่ <a href="#">นโยบายความเป็นส่วนตัว</a>' ),
			Field::make( 'text', 'pdpa_accept_text', __( 'ข้อความปุ่มยอมรับ', 'tonkla' ) )
				->set_default_value( 'ยอมรับทั้งหมด' )
				->set_width( 50 ),
			Field::make( 'text', 'pdpa_reject_text', __( 'ข้อความปุ่มปฏิเสธ', 'tonkla' ) )
				->set_default_value( 'ปฏิเสธ/ปิด' )
				->set_width( 50 ),
			Field::make( 'select', 'pdpa_policy_page_id', __( 'หน้านโยบายความเป็นส่วนตัว', 'tonkla' ) )
				->add_options( tonkla_pdpa_policy_page_options() )
				->set_help_text( __( 'เลือกได้เพียง 1 หน้า — ลิงก์ในแถบคุกกี้จะชี้ไปหน้านั้นเท่านั้น', 'tonkla' ) ),
			Field::make( 'textarea', 'pdpa_scripts', __( 'สคริปต์ที่โหลดหลังยินยอม', 'tonkla' ) )
				->set_help_text( __( 'วางโค้ด Analytics / Pixel จริงที่นี่ (โหลดหลังยินยอมเท่านั้น) ห้ามใส่ ID ตัวอย่าง', 'tonkla' ) ),
		)
	)
	->add_tab(
		__( 'โฆษณา', 'tonkla' ),
                                array(
			Field::make( 'checkbox', 'ad_sidebar_enable', __( 'แสดงโฆษณาแถบข้างบนหน้าบ้าน (บทความ)', 'tonkla' ) )
				->set_default_value( true )
				->set_help_text( __( 'ติ๊กเพื่อแสดงบนหน้าบ้าน — ตั้งค่าด้านล่างได้เสมอแม้ปิดการแสดง', 'tonkla' ) ),
			Field::make( 'select', 'ad_sidebar_type', __( 'ประเภทโฆษณา', 'tonkla' ) )
				->add_options(
					array(
						'code'  => __( 'โค้ด / AdSense', 'tonkla' ),
						'image' => __( 'แบนเนอร์รูปภาพ', 'tonkla' ),
					)
				)
				->set_default_value( 'code' ),
			Field::make( 'textarea', 'ad_sidebar_code', __( 'โค้ดโฆษณา', 'tonkla' ) )
				->set_help_text( __( 'วางโค้ด Google AdSense หรือ HTML อื่น ๆ', 'tonkla' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'ad_sidebar_type',
							'value' => 'code',
                                ),
					)
				),
			Field::make( 'image', 'ad_sidebar_image', __( 'รูปแบนเนอร์', 'tonkla' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'ad_sidebar_type',
							'value' => 'image',
						),
					)
				),
			Field::make( 'text', 'ad_sidebar_link', __( 'URL เมื่อคลิกแบนเนอร์', 'tonkla' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'ad_sidebar_type',
							'value' => 'image',
						),
					)
				),
			Field::make( 'checkbox', 'ad_sidebar_label', __( 'แสดงป้าย “โฆษณา”', 'tonkla' ) )
				->set_default_value( true ),
		)
	)
	->add_tab(
		__( 'แชท', 'tonkla' ),
                                array(
			Field::make( 'checkbox', 'chat_enable', __( 'แสดงวิดเจ็ตแชทบนหน้าบ้าน', 'tonkla' ) )
				->set_default_value( true )
				->set_help_text( __( 'ติ๊กเพื่อแสดงบนหน้าบ้าน — ตั้งค่าด้านล่างได้เสมอแม้ปิดการแสดง', 'tonkla' ) ),
			Field::make( 'textarea', 'chat_script', __( 'โค้ดฝังแชท', 'tonkla' ) )
				->set_help_text( __( 'วาง JS SDK หรือ iframe ของผู้ให้บริการแชท', 'tonkla' ) ),
		)
	)
	->add_tab(
		__( 'สคริปต์ติดตาม', 'tonkla' ),
		array(
			Field::make( 'textarea', 'header_scripts', __( 'สคริปต์ในส่วนหัว (head)', 'tonkla' ) )
				->set_help_text( __( 'โค้ดที่จะใส่ในแท็ก head เช่น Google Analytics', 'tonkla' ) )
				->set_rows( 6 ),
			Field::make( 'textarea', 'footer_scripts', __( 'สคริปต์ก่อนปิด body', 'tonkla' ) )
				->set_rows( 6 ),
		)
	);

add_action(
	'admin_notices',
	static function () {
		if ( ! isset( $_GET['import_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$status = sanitize_key( wp_unslash( $_GET['import_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'success' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'นำเข้าเสร็จแล้ว', 'tonkla' ) . '</strong> ' . esc_html__( 'สร้าง/อัปเดตหน้าที่เลือกเรียบร้อย', 'tonkla' ) . ' <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'ดูเว็บไซต์', 'tonkla' ) . ' &rarr;</a></p></div>';
		} elseif ( 'empty' === $status ) {
			echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__( 'ยังไม่ได้เลือกหน้า', 'tonkla' ) . '</strong> ' . esc_html__( 'กรุณาเลือกอย่างน้อยหนึ่งหน้าเพื่อนำเข้า', 'tonkla' ) . '</p></div>';
		}
	}
);

/**
 * Menu Item Fields
 */
Container::make( 'nav_menu_item', __( 'การตั้งค่าเมนู', 'tonkla' ) )
	->add_fields(
                                array(
			Field::make( 'text', 'icon_class', __( 'ไอคอน (FontAwesome)', 'tonkla' ) )
				->set_classes( 'tonkla-icon-field' )
				->set_help_text( __( 'กด “เลือกไอคอน” หรือพิมพ์คลาส เช่น fa-solid fa-house', 'tonkla' ) ),
			Field::make( 'select', 'icon_color', __( 'สีไอคอน', 'tonkla' ) )
				->add_options( tonkla_icon_colors() )
				->set_classes( 'tonkla-color-select' )
				->set_default_value( 'slate' ),
			Field::make( 'text', 'description', __( 'คำอธิบาย', 'tonkla' ) ),
			Field::make( 'text', 'badge_text', __( 'ข้อความป้าย', 'tonkla' ) ),
			Field::make( 'select', 'badge_color', __( 'สีป้าย', 'tonkla' ) )
				->add_options( tonkla_icon_colors() ),
			Field::make( 'separator', 'dropdown_footer_sep', __( 'ส่วนท้ายของเมนูย่อย', 'tonkla' ) ),
			Field::make( 'checkbox', 'enable_dropdown_footer', __( 'เปิดใช้ปุ่มด้านล่าง Dropdown', 'tonkla' ) )
				->set_help_text( __( 'แสดงปุ่ม “ดูทั้งหมด” ที่ด้านล่างของ dropdown (สำหรับเมนูที่มีรายการย่อย)', 'tonkla' ) )
				->set_default_value( false ),
			Field::make( 'text', 'dropdown_footer_text', __( 'ข้อความปุ่ม', 'tonkla' ) )
				->set_default_value( 'ดูบริการทั้งหมด' )
				->set_conditional_logic(
					array(
						array(
							'field' => 'enable_dropdown_footer',
							'value' => true,
						),
					)
				),
			Field::make( 'text', 'dropdown_footer_url', __( 'URL ปุ่ม', 'tonkla' ) )
				->set_help_text( __( 'ลิงก์เมื่อคลิกปุ่ม “ดูทั้งหมด”', 'tonkla' ) )
				->set_conditional_logic(
                                array(
						array(
							'field' => 'enable_dropdown_footer',
							'value' => true,
						),
					)
				),
		)
	);

/**
 * Page Builder (Flexible Content) — defined in inc/builder/page-modules.php
 */
require_once get_template_directory() . '/inc/builder/page-modules.php';
tonkla_register_page_builder_fields();

/**
 * Page/Post Display Settings
 */
Container::make( 'post_meta', __( 'การแสดงผล', 'tonkla' ) )
	->where( 'post_type', '=', 'page' )
	->or_where( 'post_type', '=', 'post' )
	->add_fields(
		array(
			Field::make( 'select', 'toc_visibility', __( 'สารบัญบทความ', 'tonkla' ) )
				->add_options(
					array(
						'default' => __( 'ตามค่าเริ่มต้นของธีม', 'tonkla' ),
						'show'    => __( 'แสดง', 'tonkla' ),
						'hide'    => __( 'ซ่อน', 'tonkla' ),
					)
				)
				->set_default_value( 'default' )
				->set_help_text( __( 'แทนที่การตั้งค่าสารบัญระดับธีมสำหรับหน้า/บทความนี้', 'tonkla' ) ),
		)
	);

/**
 * Category/Term Meta
 */
Container::make( 'term_meta', __( 'การตั้งค่าหมวดหมู่', 'tonkla' ) )
	->where( 'term_taxonomy', '=', 'category' )
	->add_fields(
		array(
			Field::make( 'text', 'custom_title', __( 'ชื่อแสดงแทน', 'tonkla' ) )
				->set_help_text( __( 'แทนชื่อหมวดหมู่เริ่มต้น เช่น “คลังความรู้”', 'tonkla' ) ),
			Field::make( 'textarea', 'custom_description', __( 'คำอธิบายกำหนดเอง', 'tonkla' ) )
				->set_rows( 3 ),
			Field::make( 'text', 'badge_text', __( 'ข้อความป้าย', 'tonkla' ) )
				->set_default_value( 'KNOWLEDGE BASE' ),
		)
	);
