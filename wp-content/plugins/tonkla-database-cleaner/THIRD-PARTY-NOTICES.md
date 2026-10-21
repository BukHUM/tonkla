# Third-Party Notices — Tonkla Database Cleaner

This plugin is a fork of **Advanced Database Cleaner 4.2.0** by SigmaPlugin (contributor: symptote), originally released under the GNU General Public License (GPL-2.0-or-later). The original copyright and license are preserved in `LICENSE.txt`.

## What is kept from upstream
- General cleanup models, handlers, validators and REST endpoint logic under `includes/` (class prefix `ADBC_*`), modified to use the `tonkla-database-cleaner` text domain and the `tonkla-db-cleaner/v1` REST namespace.

## What Tonkla wrote new
- All code under `includes/tonkla/` (namespace classes `Tonkla_DB_*`): the two-step orphan scan engine, ownership classification, safe deletion and cleanup logging, database-size analytics snapshots, settings migration bridge, and the PHP admin UI in `includes/tonkla/views/` with `assets/tonkla-admin.css` / `assets/tonkla-admin.js`.
- Upstream premium-only features were **not** copied (they are not present in the free upstream package); equivalent functionality was implemented independently as described above.

## Trademarks
"Advanced Database Cleaner" and "SigmaPlugin" are names of the upstream project/vendor and are used here only to give attribution. This fork is published as **Tonkla Database Cleaner** by Tonkla IT (https://tonkla.co) under the GPL.
