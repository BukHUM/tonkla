# Tonkla Database Cleaner

Part of **Tonkla Suite for WordPress** by [Tonkla IT](https://tonkla.co).

Clean and optimize the WordPress database safely: general cleanup (revisions, drafts, spam, expired transients, table optimization) plus an orphan scanner for options, post/user meta, transients, cron jobs and tables that do not belong to WordPress core or any active plugin/theme.

> Deleting database items is permanent. The plugin requires a database-backup confirmation before any deletion, shows a preview of every item with the reason it was classified as an orphan, locks WordPress core and active-plugin items, and logs every cleanup action.

## Requirements

- WordPress 6.0+
- PHP 8.1+
- Main site only (not multisite network-activated) in this release

## Highlights

- Two-step scan (collect + classify) stored in `wp_tonkla_db_scan_results`
- Conservative orphan detection: false negatives over false positives
- Batch-limited cleanup with logs (`wp_tonkla_db_cleanup_log`) and daily size snapshots (`wp_tonkla_db_snapshots`)
- Own PHP admin UI; no third-party JavaScript bundle

## License

GPL-2.0-or-later. This plugin is a fork of Advanced Database Cleaner 4.2.0 by SigmaPlugin — see `THIRD-PARTY-NOTICES.md` and `CHANGELOG.md`.
