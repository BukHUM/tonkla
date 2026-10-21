<?php
/** Tonkla Database Cleaner - own admin UI (replaces upstream minified SPA). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/class-tonkla-db-installer.php';
require_once __DIR__ . '/class-tonkla-db-settings.php';
require_once __DIR__ . '/class-tonkla-db-scanner.php';
require_once __DIR__ . '/class-tonkla-db-cleaner.php';

class Tonkla_DB_Admin {

	public static function register_menu() {
		add_menu_page(
			__( 'Tonkla Database Cleaner', 'tonkla-database-cleaner' ),
			__( 'TK DB Cleaner', 'tonkla-database-cleaner' ),
			'manage_options',
			'tonkla-database-cleaner',
			array( __CLASS__, 'render' ),
			'dashicons-database',
			80
		);
	}

	public static function enqueue( $hook ) {
		if ( 'toplevel_page_tonkla-database-cleaner' !== $hook ) { return; }
		wp_enqueue_style( 'tonkla-db-admin', plugins_url( 'assets/tonkla-admin.css', dirname( __DIR__, 2 ) . '/tonkla-database-cleaner.php' ), array(), TONKLA_DB_VERSION );
		wp_enqueue_script( 'tonkla-db-admin', plugins_url( 'assets/tonkla-admin.js', dirname( __DIR__, 2 ) . '/tonkla-database-cleaner.php' ), array(), TONKLA_DB_VERSION, true );
		wp_localize_script( 'tonkla-db-admin', 'tonklaDb', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'tonkla_db_nonce' ),
		) );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission.', 'tonkla-database-cleaner' ) ); }
		Tonkla_DB_Installer::maybe_install();
		global $wpdb;
		$tabs = array(
			'overview'  => __( 'Overview', 'tonkla-database-cleaner' ),
			'general'   => __( 'General Cleanup', 'tonkla-database-cleaner' ),
			'orphan'    => __( 'Orphan Scan', 'tonkla-database-cleaner' ),
			'analytics' => __( 'Size History', 'tonkla-database-cleaner' ),
			'logs'      => __( 'Cleanup Log', 'tonkla-database-cleaner' ),
		);
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		if ( ! isset( $tabs[ $tab ] ) ) { $tab = 'overview'; }

		$counts = Tonkla_DB_Cleaner::general_counts();
		$scan_table = Tonkla_DB_Installer::table( 'scan_results' );
		$snap_table = Tonkla_DB_Installer::table( 'snapshots' );
		$latest = $wpdb->get_row( "SELECT snapshot_date,total_bytes FROM $snap_table ORDER BY snapshot_date DESC LIMIT 1" );
		$summary = array(
			'general_total'   => (int) array_sum( $counts ),
			'orphan_deletable' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $scan_table WHERE belongs_to IN ('Unknown / Orphaned candidate','Expired transient')" ),
			'db_size'         => $latest ? (int) $latest->total_bytes : 0,
			'last_snapshot'   => $latest ? (string) $latest->snapshot_date : '',
		);

		$logs = array(); $snapshots = array(); $scan_types = array(); $scan_counts = array();
		if ( 'overview' === $tab ) { $logs = Tonkla_DB_Cleaner::log_rows( 5 ); }
		if ( 'logs' === $tab ) { $logs = Tonkla_DB_Cleaner::log_rows( 50 ); }
		if ( 'analytics' === $tab ) {
			$snapshots = $wpdb->get_results( "SELECT snapshot_date,total_bytes,tables_count,options_count FROM $snap_table ORDER BY snapshot_date DESC LIMIT 30" );
		}
		if ( 'orphan' === $tab ) {
			$scan_types = array( 'options' => 'Options', 'posts_meta' => 'Post meta', 'users_meta' => 'User meta', 'transients' => 'Transients', 'cron' => 'Cron jobs', 'tables' => 'Tables' );
			$rows = $wpdb->get_results( "SELECT item_type, COUNT(*) AS total, SUM(belongs_to IN ('Unknown / Orphaned candidate','Expired transient')) AS deletable FROM $scan_table GROUP BY item_type" );
			foreach ( (array) $rows as $r ) { $scan_counts[ $r->item_type ] = array( 'total' => (int) $r->total, 'deletable' => (int) $r->deletable ); }
		}
		include __DIR__ . '/views/main.php';
		self::credit();
	}

	private static function credit() {
		$url = add_query_arg( array( 'utm_source' => 'tonkla-suite', 'utm_medium' => 'credit', 'utm_campaign' => 'database-cleaner', 'utm_content' => 'admin' ), 'https://tonkla.co' );
		echo '<p class="tonkla-db-credit">' . esc_html__( 'Developed by', 'tonkla-database-cleaner' ) . ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Tonkla IT</a> — GPL-2.0-or-later. Forked from Advanced Database Cleaner 4.2.0 by SigmaPlugin (GPL).</p>';
	}

	private static function check() {
		check_ajax_referer( 'tonkla_db_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'forbidden' ), 403 ); }
	}

	public static function ajax_scan() {
		self::check();
		$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		$count = Tonkla_DB_Scanner::scan( $type );
		wp_send_json_success( array( 'count' => $count ) );
	}

	public static function ajax_list() {
		self::check();
		global $wpdb;
		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'options';
		$page = max( 1, isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1 );
		$deletable_only = ! empty( $_GET['deletable'] );
		$per  = 50; $offset = ( $page - 1 ) * $per;
		$table = Tonkla_DB_Installer::table( 'scan_results' );
		if ( $deletable_only ) {
			$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE item_type = %s AND belongs_to IN ('Unknown / Orphaned candidate','Expired transient')", $type ) );
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id,item_name,item_id,belongs_to,reason,size_bytes FROM $table WHERE item_type = %s AND belongs_to IN ('Unknown / Orphaned candidate','Expired transient') ORDER BY item_name ASC LIMIT %d OFFSET %d", $type, $per, $offset ) );
		} else {
			$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE item_type = %s", $type ) );
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id,item_name,item_id,belongs_to,reason,size_bytes FROM $table WHERE item_type = %s ORDER BY belongs_to ASC, item_name ASC LIMIT %d OFFSET %d", $type, $per, $offset ) );
		}
		wp_send_json_success( array( 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max( 1, (int) ceil( $total / $per ) ) ) );
	}

	public static function ajax_delete() {
		self::check();
		if ( empty( $_POST['backup_confirmed'] ) ) { wp_send_json_error( array( 'message' => 'backup_required' ) ); }
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array();
		wp_send_json_success( array( 'deleted' => Tonkla_DB_Cleaner::delete_scan_items( $ids ) ) );
	}

	public static function ajax_delete_all() {
		self::check();
		if ( empty( $_POST['backup_confirmed'] ) ) { wp_send_json_error( array( 'message' => 'backup_required' ) ); }
		wp_send_json_success( array( 'deleted' => Tonkla_DB_Cleaner::delete_all_deletable() ) );
	}

	public static function ajax_general() {
		self::check();
		if ( empty( $_POST['backup_confirmed'] ) ) { wp_send_json_error( array( 'message' => 'backup_required' ) ); }
		$action = isset( $_POST['action_name'] ) ? sanitize_key( wp_unslash( $_POST['action_name'] ) ) : '';
		wp_send_json_success( array( 'count' => Tonkla_DB_Cleaner::run_general( $action ), 'counts' => Tonkla_DB_Cleaner::general_counts() ) );
	}
}
add_action( 'wp_ajax_tonkla_db_scan', array( 'Tonkla_DB_Admin', 'ajax_scan' ) );
add_action( 'wp_ajax_tonkla_db_list', array( 'Tonkla_DB_Admin', 'ajax_list' ) );
add_action( 'wp_ajax_tonkla_db_delete', array( 'Tonkla_DB_Admin', 'ajax_delete' ) );
add_action( 'wp_ajax_tonkla_db_delete_all', array( 'Tonkla_DB_Admin', 'ajax_delete_all' ) );
add_action( 'wp_ajax_tonkla_db_general', array( 'Tonkla_DB_Admin', 'ajax_general' ) );
