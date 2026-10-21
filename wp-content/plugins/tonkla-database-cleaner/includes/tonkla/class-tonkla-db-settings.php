<?php
/** Tonkla Database Cleaner - settings migration bridge. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Tonkla_DB_Settings {
	/** Idempotent: copy upstream adbc_plugin_settings to tonkla_db_settings once. */
	public static function migrate() {
		if ( false !== get_option( 'tonkla_db_settings', false ) ) { return; }
		$old = get_option( 'adbc_plugin_settings', false );
		if ( is_array( $old ) ) {
			add_option( 'tonkla_db_settings', $old, '', false );
		}
	}
	public static function get( $key, $default = null ) {
		$settings = get_option( 'tonkla_db_settings', false );
		if ( ! is_array( $settings ) ) {
			$settings = get_option( 'adbc_plugin_settings', array() );
		}
		return is_array( $settings ) && array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}
}
