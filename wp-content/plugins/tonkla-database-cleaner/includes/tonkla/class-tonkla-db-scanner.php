<?php
/**
 * Tonkla Database Cleaner - own two-step scan engine (collect + classify).
 * Orphan decisions are conservative: anything matching core or an active
 * plugin/theme is never marked as an orphan candidate.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Tonkla_DB_Scanner {

	public static function core_options() {
		return array( 'siteurl','home','blogname','blogdescription','admin_email','users_can_register','default_role','timezone_string','date_format','time_format','start_of_week','use_smilies','use_balanceTags','WPLANG','template','stylesheet','current_theme','active_plugins','cron','rewrite_rules','permalink_structure','category_base','tag_base','show_on_front','page_on_front','page_for_posts','posts_per_page','default_category','default_post_format','default_pingback_flag','default_ping_status','default_comment_status','comments_per_page','thread_comments','avatar_rating','avatar_default','large_size_w','large_size_h','medium_size_w','medium_size_h','thumbnail_size_w','thumbnail_size_h','image_default_link_type','image_default_size','image_default_align','embed_size_w','embed_size_h','uploads_use_yearmonth_folders','upload_path','upload_url_path','blog_public','default_email_category','auto_core_update_notified','html_type','blog_charset','gmt_offset','initial_db_version','db_version','wp_page_for_privacy_policy','site_icon','custom_logo','nav_menu_locations','sidebars_widgets','widget_search','widget_recent-posts','widget_recent-comments','widget_archives','widget_categories','widget_meta','widget_pages','widget_calendar','widget_text','widget_rss','widget_tag_cloud','widget_nav_menu','widget_media_audio','widget_media_image','widget_media_video','widget_media_gallery','widget_custom_html','sticky_posts','recently_edited','comment_moderation','moderation_notify','comments_notify','comment_whitelist','comment_max_links','permalink_structure','gzipcompression','hack_file','blog_versions','secret','active_sitewide_plugins','_site_transient_timeout_theme_roots','fresh_site','wp_force_deactivated_plugins','uninstall_plugins','theme_switched','allowedthemes','nonce_key','logged_in_key' );
	}

	private static function owner_tokens() {
		$tokens = array();
		// Internal DB prefixes that differ from the plugin slug (Tonkla Suite stable APIs).
		$known_prefixes = array(
			'tonkla-warranty-tracker'  => array( 'itwt' ),
			'tonkla-image-optimizer'   => array( 'io' ),
			'tonkla-database-cleaner'  => array( 'adbc', 'tonkla_db' ),
		);
		foreach ( (array) get_option( 'active_plugins', array() ) as $plugin_file ) {
			$slug = dirname( $plugin_file );
			if ( '.' === $slug ) { $slug = basename( $plugin_file, '.php' ); }
			$tokens[ str_replace( '-', '_', $slug ) ] = $slug;
			$first = strtok( str_replace( '-', '_', $slug ), '_' );
			if ( $first && strlen( $first ) > 2 ) { $tokens[ $first ] = $slug; }
			foreach ( $known_prefixes[ $slug ] ?? array() as $prefix ) { $tokens[ $prefix ] = $slug; }
		}
		$theme = get_stylesheet();
		if ( $theme ) { $tokens[ str_replace( '-', '_', $theme ) ] = $theme; }
		return $tokens;
	}

	/**
	 * Field names defined by the active theme via Carbon Fields (Field::make / carbon_get_*).
	 * Carbon stores values under the plain field name (theme options) and under
	 * "_field|..." flattened keys (post/user/term meta), neither of which contains
	 * the theme slug - so slug matching alone marks live site content as orphan.
	 */
	private static $carbon_names = null;

	private static function carbon_field_names() {
		if ( null !== self::$carbon_names ) { return self::$carbon_names; }
		self::$carbon_names = array();
		$dirs = array_unique( array_filter( array( get_stylesheet_directory(), get_template_directory() ) ) );
		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $dir ) ) { continue; }
			$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
			foreach ( $it as $file ) {
				if ( 'php' !== strtolower( $file->getExtension() ) || $file->getSize() > 524288 ) { continue; }
				$code = file_get_contents( $file->getPathname() );
				if ( false === $code || false === strpos( $code, 'carbon' ) && false === strpos( $code, 'Field::make' ) ) { continue; }
				if ( preg_match_all( "/Field::make\s*\(\s*['\"][^'\"]+['\"]\s*,\s*['\"]([A-Za-z0-9_]+)['\"]/", $code, $m ) ) {
					foreach ( $m[1] as $n ) { self::$carbon_names[ $n ] = true; }
				}
				if ( preg_match_all( "/carbon_get_(?:theme_option|post_meta|user_meta|term_meta)\s*\(\s*(?:[^,()]+,\s*)?['\"]([A-Za-z0-9_]+)['\"]/", $code, $m ) ) {
					foreach ( $m[1] as $n ) { self::$carbon_names[ $n ] = true; }
				}
			}
		}
		return self::$carbon_names;
	}

	public static function classify( $name, $kind = 'option' ) {
		// Structured field storage (Carbon Fields flattened keys, serialized builders):
		// always belongs to whoever defines the field - never an orphan.
		if ( false !== strpos( $name, '|' ) ) {
			return array( (string) get_stylesheet(), 'Structured Carbon Fields value (flattened key)' );
		}
		$carbon = self::carbon_field_names();
		$base = explode( '|', ltrim( $name, '_' ) )[0];
		if ( isset( $carbon[ $name ] ) || isset( $carbon[ $base ] ) ) {
			return array( (string) get_stylesheet(), 'Carbon Fields field defined by the active theme: ' . $base );
		}
		$core = self::core_options();
		if ( 'option' === $kind && in_array( $name, $core, true ) ) {
			return array( 'WordPress Core', 'WordPress core option' );
		}
		if ( 0 === strpos( $name, '_transient_' ) || 0 === strpos( $name, '_site_transient_' ) ) {
			return array( 'WordPress Core', 'Transient storage' );
		}
		if ( 0 === strpos( $name, 'theme_mods_' ) ) {
			return array( (string) get_stylesheet(), 'Theme modifications' );
		}
		if ( 0 === strpos( $name, 'widget_' ) || 0 === strpos( $name, '_edit_' ) || in_array( $name, array( '_edit_lock', '_edit_last', '_thumbnail_id', '_wp_attached_file', '_wp_attachment_metadata', '_wp_page_template', '_menu_item_type', '_menu_item_menu_item_parent', '_menu_item_object_id', '_menu_item_object' ), true ) ) {
			return array( 'WordPress Core', 'Core / widget / post meta key' );
		}
		foreach ( self::owner_tokens() as $token => $owner ) {
			// Substring matching only for longer tokens: a 2-char token like "io"
			// appears inside unrelated words (e.g. "option") and would claim everything.
			if ( $name === $token || 0 === strpos( $name, $token . '_' ) || 0 === strpos( $name, '_' . $token . '_' ) || ( strlen( $token ) >= 4 && false !== strpos( $name, $token ) ) ) {
				return array( $owner, 'Matches active plugin/theme: ' . $owner );
			}
		}
		if ( 0 === strpos( $name, 'wp_' ) || 0 === strpos( $name, '_wp_' ) ) {
			return array( 'WordPress Core', 'Core prefix' );
		}
		return array( 'Unknown / Orphaned candidate', 'No active plugin, theme or WordPress core match found' );
	}

	public static function scan( $type ) {
		global $wpdb;
		Tonkla_DB_Installer::maybe_install();
		$table = Tonkla_DB_Installer::table( 'scan_results' );
		$wpdb->delete( $table, array( 'item_type' => $type ) );
		$rows = array();
		$now  = current_time( 'mysql' );

		if ( 'options' === $type ) {
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name AS name, LENGTH(option_value) AS size FROM {$wpdb->options} ORDER BY option_name ASC LIMIT %d", 2000 ) );
			foreach ( $data as $r ) { list( $b, $reason ) = self::classify( $r->name ); $rows[] = array( $type, $r->name, 0, $b, $reason, (int) $r->size ); }
		} elseif ( 'posts_meta' === $type || 'users_meta' === $type ) {
			$meta_table = 'posts_meta' === $type ? $wpdb->postmeta : $wpdb->usermeta;
			$data = $wpdb->get_results( "SELECT meta_key AS name, COUNT(*) AS cnt, SUM(LENGTH(meta_value)) AS size FROM {$meta_table} GROUP BY meta_key ORDER BY cnt DESC LIMIT 500" );
			foreach ( $data as $r ) { if ( '' === (string) $r->name ) { continue; } list( $b, $reason ) = self::classify( $r->name, 'meta' ); $rows[] = array( $type, $r->name, (int) $r->cnt, $b, $reason . ' (' . (int) $r->cnt . ' rows)', (int) $r->size ); }
		} elseif ( 'transients' === $type ) {
			$data = $wpdb->get_results( "SELECT option_name AS name, LENGTH(option_value) AS size FROM {$wpdb->options} WHERE option_name LIKE '_transient\\_%' AND option_name NOT LIKE '_transient\\_timeout\\_%' LIMIT 1000" );
			foreach ( $data as $r ) {
				$key = substr( $r->name, strlen( '_transient_' ) );
				$timeout = (int) get_option( '_transient_timeout_' . $key, 0 );
				$expired = $timeout > 0 && $timeout < time();
				$rows[] = array( $type, $key, 0, $expired ? 'Expired transient' : 'Transient', $expired ? 'Timeout has passed' : 'Not expired yet', (int) $r->size );
			}
		} elseif ( 'cron' === $type ) {
			$cron = get_option( 'cron' );
			if ( is_array( $cron ) ) {
				foreach ( $cron as $timestamp => $hooks ) {
					if ( ! is_array( $hooks ) ) { continue; }
					foreach ( array_keys( $hooks ) as $hook ) {
						list( $b, $reason ) = self::classify( (string) $hook, 'cron' );
						if ( 0 === strpos( (string) $hook, 'wp_' ) ) { $b = 'WordPress Core'; $reason = 'Core cron hook'; }
						$rows[] = array( $type, (string) $hook, 0, $b, $reason, 0 );
					}
				}
			}
		} elseif ( 'tables' === $type ) {
			// Authoritative core table list from wpdb itself (includes termmeta).
			$core_suffixes = array_values( (array) $wpdb->tables );
			$data = $wpdb->get_results( $wpdb->prepare( 'SELECT table_name AS name, data_length + index_length AS size FROM information_schema.tables WHERE table_schema = %s', DB_NAME ) );
			foreach ( $data as $r ) {
				$name = (string) $r->name;
				if ( 0 !== strpos( $name, $wpdb->prefix ) ) { $rows[] = array( $type, $name, 0, 'Other prefix', 'Does not use this site prefix', (int) $r->size ); continue; }
				$suffix = substr( $name, strlen( $wpdb->prefix ) );
				if ( in_array( $suffix, $core_suffixes, true ) ) { $rows[] = array( $type, $name, 0, 'WordPress Core', 'Core table', (int) $r->size ); continue; }
				list( $b, $reason ) = self::classify( $suffix, 'table' );
				$rows[] = array( $type, $name, 0, $b, $reason, (int) $r->size );
			}
		} else {
			return 0;
		}

		// Deduplicate by name for cron.
		$seen = array(); $unique = array();
		foreach ( $rows as $row ) { $k = $row[1] . '|' . $row[2]; if ( isset( $seen[ $k ] ) ) { continue; } $seen[ $k ] = true; $unique[] = $row; }

		foreach ( array_chunk( $unique, 200 ) as $chunk ) {
			$values = array(); $params = array();
			foreach ( $chunk as $row ) {
				$values[] = '(%s,%s,%d,%s,%s,%d,%s)';
				array_push( $params, $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $now );
			}
			if ( $values ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO $table (item_type,item_name,item_id,belongs_to,reason,size_bytes,scanned_at) VALUES " . implode( ',', $values ) . ' ON DUPLICATE KEY UPDATE belongs_to=VALUES(belongs_to),reason=VALUES(reason),size_bytes=VALUES(size_bytes),scanned_at=VALUES(scanned_at)', $params ) );
			}
		}
		return count( $unique );
	}
}
