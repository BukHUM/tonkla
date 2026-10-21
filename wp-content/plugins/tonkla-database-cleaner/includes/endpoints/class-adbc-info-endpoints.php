<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Info Endpoints.
 * 
 * This class provides the endpoints (controllers) for the info routes.
 */
class ADBC_Info_Endpoints {

	/**
	 * Get the system info.
	 * 
	 * @return WP_REST_Response The system info.
	 */
	public static function get_system_information() {

		try {

			global $wpdb;
			$settings = ADBC_Settings::instance()->get_settings();

			// Get locale info.
			$locale = get_locale();

			// Get theme info
			$theme_data = wp_get_theme();
			$theme = $theme_data->get( 'Name' ) . ' ' . $theme_data->get( 'Version' );
			$parent_theme = "N/A";
			if ( $theme_data->parent() ) {
				$parent_theme_data = $theme_data->parent();
				$parent_theme = $parent_theme_data->get( 'Name' ) . ' ' . $parent_theme_data->get( 'Version' );
			}

			$adbc_plugin_type = ADBC_VERSION_TYPE;
			$adbc_plugin_type = ADBC_IS_PRO_VERSION ? 'PRO' : $adbc_plugin_type;

			$data = array(
				'wordpress' => array(
					'title' => __( 'WordPress', 'tonkla-database-cleaner' ),
					'generated_on' => date( 'Y-m-d H:i:s' ),
					'data' => array(
						__( 'Version', 'tonkla-database-cleaner' ) . ": " . get_bloginfo( 'version' ),
						__( 'Site URL', 'tonkla-database-cleaner' ) . ": " . site_url(),
						__( 'Home URL', 'tonkla-database-cleaner' ) . ": " . home_url(),
						__( 'Multisite', 'tonkla-database-cleaner' ) . ": " . ( is_multisite() ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
						__( 'Sites', 'tonkla-database-cleaner' ) . ": " . ADBC_Sites::instance()->count_sites_in_multisite(),
						__( 'Language', 'tonkla-database-cleaner' ) . ": " . ( ! empty( $locale ) ? $locale : 'en_US' ),
						__( 'Theme', 'tonkla-database-cleaner' ) . ": " . $theme,
						__( 'Parent theme', 'tonkla-database-cleaner' ) . ": " . $parent_theme,
						__( 'Memory limit', 'tonkla-database-cleaner' ) . ": " . ( defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'N/A' ),
						__( 'Autoload warning threshold', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_bytes( ADBC_Options::get_autoload_warning_limit() )
					)
				),
				'database' => array(
					'title' => __( 'Database', 'tonkla-database-cleaner' ),
					'data' => array(
						__( 'Database size', 'tonkla-database-cleaner' ) . ": " . ADBC_Database::get_database_size_sql(),
						__( 'Total tables', 'tonkla-database-cleaner' ) . ": " . ADBC_Tables::get_total_tables_count() . " (" . ADBC_Tables::get_total_tables_with_invalid_prefix_count() . " " . __( 'with invalid prefix', 'tonkla-database-cleaner' ) . ")",
						__( 'Total options', 'tonkla-database-cleaner' ) . ": " . ADBC_Options::get_total_options_count(),
						__( 'Total cron jobs', 'tonkla-database-cleaner' ) . ": " . ADBC_Cron_Jobs::get_total_cron_jobs_count(),
						__( 'Total transients', 'tonkla-database-cleaner' ) . ": " . ADBC_Transients::get_total_transients_count(),
						__( 'Total user meta', 'tonkla-database-cleaner' ) . ": " . ADBC_Users_Meta::get_total_users_meta_count(),
						__( 'Total post meta', 'tonkla-database-cleaner' ) . ": " . ADBC_Posts_Meta::get_total_posts_meta_count(),
						__( 'Total post types', 'tonkla-database-cleaner' ) . ": " . ADBC_Post_Types::get_total_post_types_count(),
					)
				),
				'adbc' => array(
					'title' => 'Advanced DB Cleaner',
					'data' => array(
						__( 'Version', 'tonkla-database-cleaner' ) . ": " . ADBC_PLUGIN_VERSION . ' - ' . $adbc_plugin_type,
						__( 'Installed on', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_date_friendly( $settings['installed_on'] ),
						__( 'License status', 'tonkla-database-cleaner' ) . ": " . ( ADBC_VERSION_TYPE === 'PREMIUM' ? ADBC_License_Manager::get_license_status() : 'adbc_hidden' ),
						__( 'License plan', 'tonkla-database-cleaner' ) . ": " . ( ADBC_VERSION_TYPE === 'PREMIUM' ? ADBC_License_Manager::get_plan_name() : 'adbc_hidden' ),
						__( 'License expiry/renewal', 'tonkla-database-cleaner' ) . ": " . ( ADBC_VERSION_TYPE === 'PREMIUM' ? ADBC_License_Manager::get_license_expiration() : 'adbc_hidden' ),
					)
				),
				'adbc_settings' => array(
					'title' => __( 'Settings', 'tonkla-database-cleaner' ),
					'data' => array(
						__( 'Cleanup method', 'tonkla-database-cleaner' ) . ": " . $settings['sql_or_native_cleanup_method'],
						__( 'Database rows batches', 'tonkla-database-cleaner' ) . ": " . $settings['database_rows_batch'],
						__( 'Reduce CPU usage', 'tonkla-database-cleaner' ) . ": " . ( $settings['reduce_cpu_usage'] === '1' ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
						__( 'CPU work time', 'tonkla-database-cleaner' ) . ": " . $settings['cpu_work_time_ms'],
						__( 'CPU rest time', 'tonkla-database-cleaner' ) . ": " . $settings['cpu_rest_time_ms'],
						__( 'File line batches', 'tonkla-database-cleaner' ) . ": " . $settings['file_lines_batch'],
						__( 'File content chunks', 'tonkla-database-cleaner' ) . ": " . $settings['file_content_chunks'],
						__( 'Scan max execution time', 'tonkla-database-cleaner' ) . ": " . $settings['scan_max_execution_time'],
						__( 'Analytics enabled', 'tonkla-database-cleaner' ) . ": " . ( $settings['analytics_enabled'] === '1' ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
						__( 'Addons activity enabled', 'tonkla-database-cleaner' ) . ": " . ( $settings['addons_activity_enabled'] === '1' ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
						__( 'Show tables with invalid prefix', 'tonkla-database-cleaner' ) . ": " . ( $settings['show_tables_with_invalid_prefix'] === '1' ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
						__( 'Last successful analytics execution', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_timestamp_friendly( $settings['analytics_execution']['success'] ),
						__( 'Last failed analytics execution', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_timestamp_friendly( $settings['analytics_execution']['fail'] ),
						__( 'Last successful addons activity execution', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_timestamp_friendly( $settings['addons_activity_execution']['success'] ),
						__( 'Last failed addons activity execution', 'tonkla-database-cleaner' ) . ": " . ADBC_Common_Utils::format_timestamp_friendly( $settings['addons_activity_execution']['fail'] ),
					)
				),
				'webserver' => array(
					'title' => __( 'Webserver', 'tonkla-database-cleaner' ),
					'data' => array(
						__( 'PHP version', 'tonkla-database-cleaner' ) . ": " . phpversion(),
						__( 'MySQL version', 'tonkla-database-cleaner' ) . ": " . $wpdb->db_version(),
						__( 'Server', 'tonkla-database-cleaner' ) . ": " . ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : 'N/A' ),
						"HTTPS: " . ( isset( $_SERVER['HTTPS'] ) ? __( 'Yes', 'tonkla-database-cleaner' ) : __( 'No', 'tonkla-database-cleaner' ) ),
					)
				),
				'php' => array(
					'title' => __( 'PHP', 'tonkla-database-cleaner' ),
					'data' => array(
						__( 'Memory Limit', 'tonkla-database-cleaner' ) . ": " . ini_get( 'memory_limit' ),
						__( 'Upload Max Filesize', 'tonkla-database-cleaner' ) . ": " . ini_get( 'upload_max_filesize' ),
						__( 'Post Max Size', 'tonkla-database-cleaner' ) . ": " . ini_get( 'post_max_size' ),
						__( 'Max execution time', 'tonkla-database-cleaner' ) . ": " . ini_get( 'max_execution_time' ),
						"cURL: " . ( function_exists( 'curl_init' ) ? __( 'Supported', 'tonkla-database-cleaner' ) : __( 'Not supported', 'tonkla-database-cleaner' ) ),
					)
				),
				'dropins' => array(
					'title' => __( 'Drop-ins', 'tonkla-database-cleaner' ),
					'data' => ADBC_Plugins::instance()->get_dropins_list()
				),
				'muPlugins' => array(
					'title' => __( 'Must-use plugins', 'tonkla-database-cleaner' ),
					'data' => ADBC_Plugins::instance()->get_mu_plugins_list()
				),
				'activePlugins' => array(
					'title' => __( 'Active plugins', 'tonkla-database-cleaner' ),
					'data' => ADBC_Plugins::instance()->get_plugins_list( 'active' )
				),
				'inactivePlugins' => array(
					'title' => __( 'Inactive plugins', 'tonkla-database-cleaner' ),
					'data' => ADBC_Plugins::instance()->get_plugins_list( 'inactive' )
				)
			);

			// If multisite, add the 'networkActivePlugins' key
			if ( is_multisite() ) {
				$data['networkActivePlugins'] = array(
					'title' => __( 'Network active plugins', 'tonkla-database-cleaner' ),
					'data' => ADBC_Plugins::instance()->get_network_active_plugins_list()
				);
			}

			return ADBC_Rest::success( "", $data );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}
	}

}