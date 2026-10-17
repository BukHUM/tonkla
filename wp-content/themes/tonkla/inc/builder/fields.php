<?php
/**
 * Page Builder field factories (Carbon Fields).
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Field;

/**
 * Module info HTML (wireframe + description) for the first field of a group.
 *
 * @param string $slug Module slug from tonkla_builder_modules().
 * @return \Carbon_Fields\Field\Field
 */
function tonkla_module_info_field( $slug ) {
	$meta = tonkla_builder_module( $slug );
	if ( ! $meta ) {
		return Field::make( 'html', 'module_info_' . $slug )->set_html( '' );
	}

	$wireframe = esc_url( $meta['wireframe'] );
	$label     = esc_html( $meta['label'] );
	$desc      = esc_html( $meta['description'] );

	$html  = '<div class="tonkla-module-info">';
	$html .= '<div class="tonkla-module-info__preview">';
	$html .= '<img src="' . $wireframe . '" alt="" width="280" height="140" loading="lazy" />';
	$html .= '</div>';
	$html .= '<div class="tonkla-module-info__body">';
	$html .= '<strong class="tonkla-module-info__title">' . $label . '</strong>';
	$html .= '<p class="tonkla-module-info__desc">' . $desc . '</p>';
	$html .= '</div></div>';

	return Field::make( 'html', 'module_info_' . sanitize_key( $slug ) )
		->set_html( $html );
}

/**
 * Section header fields (title / subtitle / description).
 *
 * @param array<string,mixed> $args Optional overrides.
 * @return array<int,\Carbon_Fields\Field\Field>
 */
function tonkla_section_header_fields( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title_key'       => 'section_title',
			'subtitle_key'    => 'section_subtitle',
			'description_key' => 'section_description',
			'title_label'     => __( 'หัวข้อส่วน', 'tonkla' ),
			'subtitle_label'  => __( 'หัวข้อรอง', 'tonkla' ),
			'desc_label'      => __( 'คำอธิบาย', 'tonkla' ),
			'include_subtitle'=> true,
			'include_desc'    => true,
			'title_default'   => '',
			'desc_rows'       => 3,
		)
	);

	$fields = array(
		Field::make( 'text', $args['title_key'], $args['title_label'] ),
	);
	if ( '' !== $args['title_default'] ) {
		$fields[0]->set_default_value( $args['title_default'] );
	}
	if ( $args['include_subtitle'] ) {
		$fields[] = Field::make( 'text', $args['subtitle_key'], $args['subtitle_label'] );
	}
	if ( $args['include_desc'] ) {
		$fields[] = Field::make( 'textarea', $args['description_key'], $args['desc_label'] )
			->set_rows( (int) $args['desc_rows'] );
	}

	return $fields;
}

/**
 * Background color select.
 *
 * @param string $default Default slug.
 * @return \Carbon_Fields\Field\Field
 */
function tonkla_bg_color_field( $default = 'white' ) {
	return Field::make( 'select', 'bg_color', __( 'สีพื้นหลัง', 'tonkla' ) )
		->add_options( tonkla_bg_color_options() )
		->set_classes( 'tonkla-color-select' )
		->set_default_value( $default );
}

/**
 * Icon fields: type + FA class + color + optional image upload.
 *
 * Back-compat: when $class_key is 'icon', stores legacy field name used by existing modules.
 *
 * @param array<string,mixed> $args Options.
 * @return array<int,\Carbon_Fields\Field\Field>
 */
function tonkla_icon_fields( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'type_key'       => 'icon_type',
			'class_key'      => 'icon',
			'color_key'      => 'icon_color',
			'image_key'      => 'icon_image',
			// Use "icon" (not "fa") to match legacy Tech Stack stored values.
			'default_type'   => 'icon',
			'default_class'  => '',
			'default_color'  => 'brand',
			'include_image'  => true,
			'include_color'  => true,
			'color_options'  => null,
			'type_label'     => __( 'ประเภทไอคอน', 'tonkla' ),
			'class_label'    => __( 'ไอคอน (FontAwesome)', 'tonkla' ),
			'color_label'    => __( 'สีไอคอน', 'tonkla' ),
			'image_label'    => __( 'อัปโหลดไอคอน', 'tonkla' ),
			'help_class'     => __( 'กด “เลือกไอคอน” หรือพิมพ์คลาส เช่น fa-solid fa-server', 'tonkla' ),
		)
	);

	$color_options = is_array( $args['color_options'] ) ? $args['color_options'] : tonkla_icon_colors();

	$fields = array(
		Field::make( 'select', $args['type_key'], $args['type_label'] )
			->add_options(
				array(
					'icon'  => __( 'FontAwesome', 'tonkla' ),
					'image' => __( 'อัปโหลดรูป / SVG', 'tonkla' ),
				)
			)
			->set_default_value( $args['default_type'] ),
		Field::make( 'text', $args['class_key'], $args['class_label'] )
			->set_help_text( $args['help_class'] )
			->set_classes( 'tonkla-icon-field' )
			->set_conditional_logic(
				array(
					array(
						'field' => $args['type_key'],
						'value' => 'icon',
					),
				)
			),
	);

	if ( '' !== $args['default_class'] ) {
		$fields[1]->set_default_value( $args['default_class'] );
	}

	if ( $args['include_color'] ) {
		$color = Field::make( 'select', $args['color_key'], $args['color_label'] )
			->add_options( $color_options )
			->set_classes( 'tonkla-color-select' )
			->set_default_value( $args['default_color'] )
			->set_conditional_logic(
				array(
					array(
						'field' => $args['type_key'],
						'value' => 'icon',
					),
				)
			);
		$fields[] = $color;
	}

	if ( $args['include_image'] ) {
		$fields[] = Field::make( 'image', $args['image_key'], $args['image_label'] )
			->set_help_text( __( 'รองรับ PNG, WebP หรือ SVG (เฉพาะผู้ดูแลระบบ)', 'tonkla' ) )
			->set_conditional_logic(
				array(
					array(
						'field' => $args['type_key'],
						'value' => 'image',
					),
				)
			);
	}

	return $fields;
}

/**
 * Link fields: type + URL or association.
 *
 * @param array<string,mixed> $args Options.
 * @return array<int,\Carbon_Fields\Field\Field>
 */
function tonkla_link_fields( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'type_key'        => 'link_type',
			'url_key'         => 'link',
			'content_key'     => 'link_content',
			'default_type'    => 'custom',
			'type_label'      => __( 'ประเภทลิงก์', 'tonkla' ),
			'url_label'       => __( 'URL', 'tonkla' ),
			'content_label'   => __( 'เลือกหน้า / โพสต์', 'tonkla' ),
			'extra_conditions'=> array(),
		)
	);

	$type_conditions = $args['extra_conditions'];
	$url_conditions  = array_merge(
		$args['extra_conditions'],
		array(
			array(
				'field' => $args['type_key'],
				'value' => 'custom',
			),
		)
	);
	$post_conditions = array_merge(
		$args['extra_conditions'],
		array(
			array(
				'field' => $args['type_key'],
				'value' => 'post',
			),
		)
	);

	$type_field = Field::make( 'select', $args['type_key'], $args['type_label'] )
		->add_options( tonkla_button_link_type_options() )
		->set_default_value( $args['default_type'] );

	if ( ! empty( $type_conditions ) ) {
		$logic = $type_conditions;
		if ( count( $logic ) > 1 ) {
			$logic = array_merge( array( 'relation' => 'AND' ), $logic );
		}
		$type_field->set_conditional_logic( $logic );
	}

	$url_logic = $url_conditions;
	if ( count( $url_logic ) > 1 ) {
		$url_logic = array_merge( array( 'relation' => 'AND' ), $url_logic );
	}

	$url_field = Field::make( 'text', $args['url_key'], $args['url_label'] )
		->set_attribute( 'placeholder', 'https://' )
		->set_conditional_logic( $url_logic );

	$post_logic = $post_conditions;
	if ( count( $post_logic ) > 1 ) {
		$post_logic = array_merge( array( 'relation' => 'AND' ), $post_logic );
	}

	$content_field = Field::make( 'association', $args['content_key'], $args['content_label'] )
		->set_types(
			array(
				array(
					'type'      => 'post',
					'post_type' => 'page',
				),
				array(
					'type'      => 'post',
					'post_type' => 'post',
				),
			)
		)
		->set_max( 1 )
		->set_conditional_logic( $post_logic );

	return array( $type_field, $url_field, $content_field );
}
