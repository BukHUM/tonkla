<?php
/**
 * Featured image column in admin post/page lists.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Insert Thumbnail column after the checkbox for post and page lists.
 *
 * @param array  $columns   Existing columns.
 * @param string $post_type Post type slug (passed by manage_posts_columns).
 * @return array
 */
function tonkla_admin_thumbnail_columns( $columns, $post_type = '' ) {
	// manage_posts_columns applies to every non-hierarchical type — keep post only.
	if ( '' !== $post_type && 'post' !== $post_type ) {
		return $columns;
	}

	$new_columns = array();

	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;

		if ( 'cb' === $key ) {
			$new_columns['tonkla_thumbnail'] = __( 'Thumbnail', 'tonkla' );
		}
	}

	return $new_columns;
}
add_filter( 'manage_posts_columns', 'tonkla_admin_thumbnail_columns', 10, 2 );
add_filter( 'manage_pages_columns', 'tonkla_admin_thumbnail_columns' );

/**
 * Render featured image in the Thumbnail column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function tonkla_admin_thumbnail_column_content( $column, $post_id ) {
	if ( 'tonkla_thumbnail' !== $column ) {
		return;
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id > 0 ) {
		echo wp_get_attachment_image(
			$thumbnail_id,
			array( 60, 60 ),
			false,
			array(
				'class'   => 'tonkla-admin-thumbnail',
				'alt'     => '',
				'loading' => 'lazy',
			)
		);
		return;
	}

	echo '<span class="tonkla-admin-thumbnail-empty" aria-hidden="true">' . esc_html( '—' ) . '</span>';
}
add_action( 'manage_posts_custom_column', 'tonkla_admin_thumbnail_column_content', 10, 2 );
add_action( 'manage_pages_custom_column', 'tonkla_admin_thumbnail_column_content', 10, 2 );
