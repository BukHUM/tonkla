=== Tonkla Database Cleaner ===
Contributors: tonkla
Tags: database, cleanup, optimize, orphan, transients
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean and optimize the WordPress database safely with orphan scanning. Part of Tonkla Suite by Tonkla IT.

== Description ==

Tonkla Database Cleaner removes database clutter (old revisions, auto drafts, trashed/spam posts and comments, expired transients) and optimizes tables. Its two-step orphan scanner classifies options, post meta, user meta, transients, cron jobs and tables by owner; items that do not match WordPress core or any active plugin/theme are shown as orphan candidates with a reason before you decide to delete them.

Safety first: deleting is permanent, so the plugin requires a database-backup confirmation, locks WordPress core and active-plugin items, deletes in limited batches, and logs every cleanup action.

This plugin is a fork of Advanced Database Cleaner 4.2.0 by SigmaPlugin (GPL). Premium-level functionality was implemented independently by Tonkla IT. See THIRD-PARTY-NOTICES.md in the plugin folder.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/tonkla-database-cleaner/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tonkla - Database Cleaner, back up your database, then run a scan

== Changelog ==

= 1.2.2 =
* Critical fix: Carbon Fields data (theme options like main_logo, flattened _page_modules|... post meta) was misclassified as orphaned because those keys contain no plugin/theme slug; the scanner now recognizes structured Carbon keys and the field names the active theme actually defines, and every item is re-classified at delete time before removal.
* "Delete ALL deletable items" now covers options, transients and cron only; post/user meta require review-first deletion like tables.

= 1.2.1 =
* Critical fix: wp_termmeta could be misclassified as an orphan table and dropped (hardcoded core list missed termmeta; now uses $wpdb->tables), and active plugins whose DB prefix differs from their slug (e.g. itwt_*) were not recognized as owners. Core tables are now hard-locked at delete time and tables are re-classified before dropping.
* "Delete ALL deletable items" no longer drops tables; tables are deleted one by one from Orphan Scan > Tables after review.

= 1.2.0 =
* Added "Delete ALL deletable items": a danger-zone button on Overview and a button in the Orphan Scan action bar that remove every orphan candidate and expired item across all types in one action, with backup confirmation, double confirm, core/active items locked, and full cleanup logging.
* Removed the duplicated summary strip above the tabs; the stat numbers now appear only in the Overview cards.

= 1.1.1 =
* Table styling standardized: fixed checkbox/count/action column widths, consistent cell padding and vertical alignment, right-aligned numeric columns, and uniform group header rows across the General Cleanup and Orphan Scan tables.

= 1.1.0 =
* Admin UI reorganized into tabs (Overview, General Cleanup, Orphan Scan, Size History, Cleanup Log) with a summary strip and a sticky backup-confirmation bar on every tab; scan types are a segmented control showing deletable counts, general cleanup is grouped, and the orphan table has a sticky action bar with a selected-items counter.

= 1.0.4 =
* Orphan scan UX: removed the redundant type selector and Load button (scan buttons now drive the table), and added a "Show deletable items only" filter, on by default, so orphan candidates are visible without paging through items that cannot be deleted.

= 1.0.3 =
* General cleanup can now be selected with checkboxes (including select all) and run several cleanup tasks together with one backup confirmation, running sequentially with progress status and refreshed counts.

= 1.0.2 =
* Fixed a JavaScript syntax error in the admin script (extra closing brace on the pagination Next button handler) that prevented all admin interactions.

= 1.0.1 =
* Admin menu title shortened to "TK DB Cleaner" (page title remains Tonkla Database Cleaner).

= 1.0.0 =
* First Tonkla Suite release: fork of Advanced Database Cleaner 4.2.0 (GPL) with new PHP admin UI, own orphan scan engine, safe deletion with backup confirmation and cleanup logs, database-size snapshots, and removal of upstream free-version limits and remote/license code paths.
