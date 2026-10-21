# Changelog — Tonkla Database Cleaner

All notable changes to this plugin are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/), versions follow Semantic Versioning.

## [1.2.2] - 2026-10-21

### Fixed
- Critical classifier flaw behind the "empty homepage" incident: Carbon Fields stores theme options under plain field names (`main_logo`, `footer_show_credit`, ...) and post meta under flattened `_page_modules|...` keys, none of which contain the theme/plugin slug, so slug-based owner matching marked live site content as orphan candidates and it was deleted. The scanner now (a) treats any key containing `|` as structured Carbon Fields storage owned by the active theme, and (b) collects the Carbon field names actually defined by the active theme (`Field::make(...)` / `carbon_get_*()` in theme files) and classifies those options/meta as theme-owned.
- Substring owner matching now requires tokens of at least 4 characters: the 2-char `io` token matched unrelated names (e.g. inside "option") and claimed everything.
- `delete_scan_items()` now re-classifies **every** item type at delete time (previously only tables), so stale scan rows can no longer delete anything that currently belongs to core or an active plugin/theme.

### Changed
- "Delete ALL deletable items" now covers options, transients and cron jobs only. Post/user meta joined tables on the review-first list: meta rows are invisible field storage whose loss only shows up as empty pages.

### Site repair (local dev, tonkla.local)
- Restored the deleted content from the UpdraftPlus backup of 2026-10-16 (`backup_2026-10-16-1502_...-db.gz`) with a surgical, insert-missing-only import (temporary database, no current rows overwritten): postmeta 345 -> 1,660 rows, options 293 -> 777, usermeta -> 76, termmeta -> 10, plus ITWT customers (66), assets (1) and contracts (1) re-imported with explicit column lists. A full dump of the pre-restore database was saved outside the repo first. Homepage renders fully again and the Carbon Fields "Undefined array key 3" warning is gone.

## [1.2.1] - 2026-10-21

### Fixed
- Critical scanner bug: the hardcoded core-table suffix list missed `termmeta`, so `wp_termmeta` was classified as an orphan candidate and could be dropped. The core table list now comes from `$wpdb->tables` (the authoritative WordPress list, which includes `termmeta`).
- Scanner owner detection missed plugins whose database prefix differs from their folder slug (e.g. Tonkla Warranty Tracker stores `itwt_*` tables/options), so an active plugin's tables were shown as orphan candidates and dropped. Added a known-prefix map for Tonkla Suite plugins (`itwt`, `io`, `adbc`/`tonkla_db`).
- Defense in depth in `Tonkla_DB_Cleaner::delete_scan_items()`: core tables (prefixed `$wpdb->tables`) are hard-locked and every table is re-classified at delete time against the current active plugin list, so a stale or forged scan row can no longer drop a core/active table.

### Changed
- "Delete ALL deletable items" no longer drops tables. Tables must be deleted one by one from Orphan Scan > Tables after review; the Overview danger-zone text now states this.

### Site repair (local dev, tonkla.local)
- Recreated the dropped `wp_termmeta` core table with the standard WordPress schema. The `wp_itwt_*` tables dropped in the same incident were already recreated (empty) by the Warranty Tracker's installer. Orphan data deleted before this fix (inactive WooCommerce/AIOS/Rank Math leftovers) is not restored.

## [1.2.0] - 2026-10-21

### Added
- "Delete ALL deletable items" button in two places: a danger-zone panel on the Overview tab (showing the deletable count) and the Orphan Scan action bar next to "Delete selected orphans". It deletes every orphan candidate and expired item from the latest scans across all types in one confirmed action (new `wp_ajax_tonkla_db_delete_all` endpoint and `Tonkla_DB_Cleaner::delete_all_deletable()`).

### Changed
- Removed the summary strip above the tabs: it duplicated the Overview stat cards, so the numbers now live in exactly one place (the Overview cards).
- The clear-all flow keeps every existing safeguard: backup confirmation required, double confirm dialog, WordPress core and active plugin/theme items stay locked, deletions are logged, and the page reloads afterwards so counts refresh.

## [1.1.2] - 2026-10-21

### Fixed
- Table header alignment: header/body checkboxes in checkable tables are rendered as centered block elements with middle vertical alignment, so the select-all checkbox now lines up with the "Item" label instead of floating above the text baseline.

## [1.1.1] - 2026-10-21

### Fixed
- Standardized table layout in the admin screen: fixed widths for the checkbox (36px), count (150px, right-aligned, tabular numbers) and action (130px, right-aligned) columns, uniform 8px/10px cell padding with middle vertical alignment, and consistent group header rows — fixing the uneven spacing and misaligned header checkbox in General Cleanup and Orphan Scan tables.

## [1.1.0] - 2026-10-21

### Changed
- Admin screen reorganized from one long scrolling page into server-side tabs (Overview, General Cleanup, Orphan Scan, Size History, Cleanup Log) selected via the `tab` query parameter, each loading only its own data.
- Added a summary strip (general cleanup items, orphan candidates, database size, last snapshot) and a sticky backup-confirmation bar visible on every tab, plus overview cards linking to each section and the 5 most recent cleanup log entries.
- Orphan scan types are now a segmented control showing "deletable / total" counts per type; the Delete action bar and table header are sticky with a live selected-items counter.
- General cleanup rows are grouped (Content, Comments, System, Maintenance) with per-group select checkboxes.

## [1.0.4] - 2026-10-21

### Changed
- Orphan scan UX: removed the redundant "Show" type selector and "Load results" button (the scan buttons above the table now select and load the type), and added a **"Show deletable items only"** filter (enabled by default) so orphan candidates and expired items are listed immediately instead of being buried across many pages of non-deletable items. Untick it to review every scanned item.

## [1.0.3] - 2026-10-21

### Added
- General cleanup multi-select: checkboxes per item plus select-all and a "Clean selected" button that runs the chosen cleanup tasks sequentially with a single backup confirmation, shows progress, refreshes counts and logs each task as before. Single-item Clean buttons remain available.

## [1.0.2] - 2026-10-21

### Fixed
- JavaScript syntax error in `assets/tonkla-admin.js` (extra closing brace in the pagination "Next" handler) that broke every admin interaction on the Tonkla Database Cleaner screen.

## [1.0.1] - 2026-10-21

### Changed
- Admin menu title shortened from "Tonkla - Database Cleaner" to **"TK DB Cleaner"** so it fits on one line in the admin menu; the page title remains "Tonkla Database Cleaner".

## [1.0.0] - 2026-10-21

### Added
- New Tonkla admin UI (PHP views with `assets/tonkla-admin.css` / `assets/tonkla-admin.js`) replacing the upstream minified JavaScript bundle: general cleanup, orphan scan results with pagination, database-size history and cleanup log on one screen.
- Own two-step orphan scan engine (`includes/tonkla/`): collects and classifies options, post meta, user meta, transients, cron jobs and tables into `wp_tonkla_db_scan_results`, marking items that do not match WordPress core or any active plugin/theme as orphan candidates with a reason. Core and active-plugin items are locked from deletion.
- Safe deletion requiring a database-backup confirmation, batch-limited general cleanup, cleanup logging to `wp_tonkla_db_cleanup_log`, and daily database-size snapshots to `wp_tonkla_db_snapshots`.
- Guard that prevents running alongside the upstream Advanced Database Cleaner (any edition).

### Changed
- Forked from **Advanced Database Cleaner 4.2.0** (SigmaPlugin, GPL-2.0-or-later) and rebranded as Tonkla Database Cleaner, part of Tonkla Suite: slug/text domain `tonkla-database-cleaner`, REST namespace `tonkla-db-cleaner/v1`.
- Settings migrate automatically from `adbc_plugin_settings` to `tonkla_db_settings` (legacy data kept). Upstream FREE limits no longer apply, upstream remote API/upsell/license code paths are removed or disconnected.

### Kept
- General cleanup models, handlers, validators and REST endpoint logic from upstream under `includes/` (`ADBC_*`), see `THIRD-PARTY-NOTICES.md`.
