<?php
/**
 * Keep large theme options out of the autoloaded options bag.
 *
 * @package Tonkla
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option names that may hold large script/HTML payloads.
 *
 * @return string[]
 */
function tonkla_large_option_names() {
	return array(
		'header_scripts',
		'_header_scripts',
		'footer_scripts',
		'_footer_scripts',
		'chat_script',
		'_chat_script',
		'pdpa_scripts',
		'_pdpa_scripts',
		'ad_sidebar_code',
		'_ad_sidebar_code',
	);
}

/**
 * Force autoload=no for known large options.
 */
function tonkla_disable_large_option_autoload() {
	global $wpdb;

	foreach ( tonkla_large_option_names() as $option_name ) {
		$autoload = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT autoload FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
				$option_name
			)
		);

		if ( null === $autoload ) {
			continue;
		}

		if ( in_array( $autoload, array( 'yes', 'on', 'auto' ), true ) ) {
			$wpdb->update(
				$wpdb->options,
				array( 'autoload' => 'no' ),
				array( 'option_name' => $option_name ),
				array( '%s' ),
				array( '%s' )
			);
			wp_cache_delete( $option_name, 'options' );
		}
	}

	wp_cache_delete( 'alloptions', 'options' );
}
add_action( 'carbon_fields_theme_options_container_saved', 'tonkla_disable_large_option_autoload' );

/**
 * One-time migration for existing installs.
 */
function tonkla_maybe_migrate_large_option_autoload() {
	if ( get_option( 'tonkla_large_options_autoload_v1' ) ) {
		return;
	}

	tonkla_disable_large_option_autoload();
	update_option( 'tonkla_large_options_autoload_v1', 1, false );
}
add_action( 'admin_init', 'tonkla_maybe_migrate_large_option_autoload' );
