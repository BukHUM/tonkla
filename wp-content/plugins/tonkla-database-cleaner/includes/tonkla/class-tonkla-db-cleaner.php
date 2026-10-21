<?php
/** Tonkla Database Cleaner - safe deletion + general cleanup. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Tonkla_DB_Cleaner {

	private static function log( $action, $type, $name ) {
		global $wpdb;
		$wpdb->insert( Tonkla_DB_Installer::table( 'cleanup_log' ), array(
			'user_id' => get_current_user_id(), 'action_type' => $action,
			'item_type' => $type, 'item_name' => $name, 'created_at' => current_time( 'mysql' ),
		) );
	}

	public static function log_rows( $limit = 50 ) {
		global $wpdb;
		$table = Tonkla_DB_Installer::table( 'cleanup_log' );
		return $wpdb->get_results( $wpdb->prepare( "SELECT id,user_id,action_type,item_type,item_name,created_at FROM $table ORDER BY id DESC LIMIT %d", $limit ) );
	}

	/** Delete scan-result rows by ID. Only orphan candidates / expired items are deletable. */
	public static function delete_scan_items( $ids ) {
		global $wpdb;
		$table = Tonkla_DB_Installer::table( 'scan_results' );
		$deleted = 0;
		foreach ( array_map( 'absint', (array) $ids ) as $id ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT id,item_type,item_name,belongs_to FROM $table WHERE id = %d", $id ) );
			if ( ! $row ) { continue; }
			$deletable = ( 'Unknown / Orphaned candidate' === $row->belongs_to ) || ( 'Expired transient' === $row->belongs_to ) || ( 'Other prefix' === $row->belongs_to && 'tables' !== $row->item_type );
			if ( ! $deletable ) { continue; }
			// Defense in depth: never trust a stale scan row. Re-classify EVERY item at
			// delete time against the current active plugins/theme; if it no longer
			// classifies as an orphan/expired candidate, skip it.
			if ( 'transients' !== $row->item_type && class_exists( 'Tonkla_DB_Scanner' ) ) {
				$kind = array( 'options' => 'option', 'posts_meta' => 'meta', 'users_meta' => 'meta', 'cron' => 'cron', 'tables' => 'table' )[ $row->item_type ] ?? 'option';
				$fresh_name = $row->item_name;
				if ( 'tables' === $row->item_type && 0 === strpos( $fresh_name, $wpdb->prefix ) ) { $fresh_name = substr( $fresh_name, strlen( $wpdb->prefix ) ); }
				list( $fresh_owner ) = Tonkla_DB_Scanner::classify( $fresh_name, $kind );
				if ( 'Unknown / Orphaned candidate' !== $fresh_owner && 'Other prefix' !== $fresh_owner && 'Expired transient' !== $fresh_owner ) { continue; }
			}
			// Hard-lock core tables on top of the re-classification.
			if ( 'tables' === $row->item_type ) {
				$core_tables = array();
				foreach ( (array) $wpdb->tables as $core_suffix ) { $core_tables[] = $wpdb->prefix . $core_suffix; }
				if ( in_array( $row->item_name, $core_tables, true ) ) { continue; }
				if ( class_exists( 'Tonkla_DB_Scanner' ) ) {
					$suffix = 0 === strpos( $row->item_name, $wpdb->prefix ) ? substr( $row->item_name, strlen( $wpdb->prefix ) ) : $row->item_name;
					list( $fresh_owner ) = Tonkla_DB_Scanner::classify( $suffix, 'table' );
					if ( 'Unknown / Orphaned candidate' !== $fresh_owner && 'Other prefix' !== $fresh_owner ) { continue; }
				}
			}
			$ok = false;
			if ( 'options' === $row->item_type ) {
				$ok = delete_option( $row->item_name );
			} elseif ( 'posts_meta' === $row->item_type ) {
				$ok = false !== $wpdb->delete( $wpdb->postmeta, array( 'meta_key' => $row->item_name ) );
			} elseif ( 'users_meta' === $row->item_type ) {
				$ok = false !== $wpdb->delete( $wpdb->usermeta, array( 'meta_key' => $row->item_name ) );
			} elseif ( 'transients' === $row->item_type ) {
				delete_transient( $row->item_name ); $ok = true;
			} elseif ( 'cron' === $row->item_type ) {
				wp_unschedule_hook( $row->item_name ); $ok = true;
			} elseif ( 'tables' === $row->item_type ) {
				if ( preg_match( '/^[A-Za-z0-9_]+$/', $row->item_name ) && 0 === strpos( $row->item_name, $wpdb->prefix ) ) {
					$ok = false !== $wpdb->query( 'DROP TABLE IF EXISTS `' . $row->item_name . '`' );
				}
			}
			if ( $ok ) {
				$wpdb->delete( $table, array( 'id' => $id ) );
				self::log( 'delete', $row->item_type, $row->item_name );
				$deleted++;
			}
		}
		return $deleted;
	}

	/**
	 * Delete every deletable scan result (orphan candidates + expired items).
	 * Tables and post/user meta are intentionally excluded from the bulk action:
	 * dropping a table is irreversible, and meta rows are field storage (e.g.
	 * Carbon Fields) whose loss is invisible until a page renders empty.
	 * Delete those from their Orphan Scan types after review - every item is
	 * still re-classified at delete time before anything is removed.
	 */
	public static function delete_all_deletable() {
		global $wpdb;
		$table = Tonkla_DB_Installer::table( 'scan_results' );
		$ids = $wpdb->get_col( "SELECT id FROM $table WHERE belongs_to IN ('Unknown / Orphaned candidate','Expired transient') AND item_type NOT IN ('tables','posts_meta','users_meta') ORDER BY id ASC LIMIT 5000" );
		return self::delete_scan_items( array_map( 'intval', (array) $ids ) );
	}

	public static function general_counts() {
		global $wpdb;
		return array(
			'revisions'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" ),
			'auto_drafts'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" ),
			'trashed_posts'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" ),
			'spam_comments'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" ),
			'trashed_comments' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" ),
			'expired_transients' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient\\_timeout\\_%' AND option_value < UNIX_TIMESTAMP()" ),
		);
	}

	public static function run_general( $action ) {
		global $wpdb;
		$count = 0;
		if ( 'revisions' === $action ) {
			$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' LIMIT 500" );
			foreach ( $ids as $id ) { if ( wp_delete_post_revision( (int) $id ) ) { $count++; } }
		} elseif ( 'auto_drafts' === $action || 'trashed_posts' === $action ) {
			$status = 'auto_drafts' === $action ? 'auto-draft' : 'trash';
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s LIMIT 500", $status ) );
			foreach ( $ids as $id ) { if ( wp_delete_post( (int) $id, true ) ) { $count++; } }
		} elseif ( 'spam_comments' === $action || 'trashed_comments' === $action ) {
			$approved = 'spam_comments' === $action ? 'spam' : 'trash';
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = %s LIMIT 500", $approved ) );
			foreach ( $ids as $id ) { if ( wp_delete_comment( (int) $id, true ) ) { $count++; } }
		} elseif ( 'expired_transients' === $action ) {
			$count = (int) $wpdb->query( "DELETE a, b FROM {$wpdb->options} a JOIN {$wpdb->options} b ON b.option_name = CONCAT('_transient_', SUBSTRING(a.option_name, CHAR_LENGTH('_transient_timeout_') + 1)) WHERE a.option_name LIKE '_transient\\_timeout\\_%' AND a.option_value < UNIX_TIMESTAMP()" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient\\_timeout\\_%' AND option_value < UNIX_TIMESTAMP()" );
		} elseif ( 'optimize' === $action ) {
			$tables = $wpdb->get_col( $wpdb->prepare( 'SELECT table_name FROM information_schema.tables WHERE table_schema = %s', DB_NAME ) );
			foreach ( (array) $tables as $t ) { if ( preg_match( '/^[A-Za-z0-9_]+$/', (string) $t ) ) { $wpdb->query( 'OPTIMIZE TABLE `' . $t . '`' ); $count++; } }
		}
		if ( $count > 0 || 'optimize' === $action ) { self::log( $action, 'general', $action . ':' . $count ); }
		return $count;
	}
}
