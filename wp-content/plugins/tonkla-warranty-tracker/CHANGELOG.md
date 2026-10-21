# Changelog — Tonkla Warranty Tracker

All notable changes to this plugin are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/), versions follow Semantic Versioning.

## [2.0.6] - 2026-10-21

### Fixed
- Portal banner heading was nearly invisible (dark text on the dark banner): `.itwt-page__heading` set no color of its own, so the host theme's global `h1-h6 { color: ... }` rule (Tonkla theme appearance CSS) overrode the banner's inherited white. The heading now explicitly uses `#fff`, so all three portal themes (Soft / Minimal / Dense) render readable white titles regardless of the host theme's heading color.

## [2.0.5] - 2026-10-21

### Added
- Legacy QR rescue: requests to the site root with a bare `?tag=XXXX` parameter (links generated/printed while `lookup_page_id` was unconfigured, which WordPress interprets as a tag-taxonomy archive and 404s into an empty page) now 302-redirect to the configured lookup page with the tag preserved. Genuine tag archives (an existing `post_tag` term slug) and the lookup page itself are never redirected.

### Fixed
- Site repair (local dev, tonkla.local): `lookup_page_id` in `itwt_settings` had been reset to 0, so every QR/lookup URL pointed at the homepage and rendered an empty 404 template. Restored it to the published "ตรวจสอบประกัน" page (ID 353); lookup for existing tags (e.g. 9BEJRES) returns full asset details again.

## [2.0.4] - 2026-10-21

### Changed
- Developer credit is locked on: the "เครดิตผู้พัฒนา" checkbox in Settings is now rendered checked + disabled with an explanatory note, the settings save handler always stores `show_developer_credit = true`, and `\ITWT\Settings::show_developer_credit()` always returns `true`. Rationale: the plugin is free under the condition that the developer credit stays visible on admin screens and the public lookup portal (printed stickers remain credit-free, as before).

## [2.0.3] - 2026-10-21

### Changed
- Admin menu title shortened from "Tonkla - Warranty Lookup" to **"TK Warranty Lookup"** so it fits on one line in the admin menu; the page title remains "Tonkla Warranty Tracker".

## [2.0.2] - 2026-10-21

### Changed
- Admin menu renamed from "Tonkla Warranty" to **"Tonkla - Warranty Lookup"** (wraps to two lines in the 190px Tonkla admin menu instead of overflowing; the new-request count badge is unchanged).
- Menu icon reverted to the original **Dashicons** (`dashicons-shield`) for consistency with the WordPress admin; the experimental favicon-based icon (`tonkla-mark.png`) was removed.

## [2.0.1] - 2026-10-21

### Changed
- Admin menu icon ("Tonkla Warranty") now uses the Tonkla favicon as a 20×20 grayscale + inverted mark, matching the WordPress admin sidebar and the site-icon treatment in the admin bar — easier to see on the dark menu and sized exactly to WordPress menu-icon dimensions.

## [2.0.0] - 2026-10-21

### Changed
- Renamed to **Tonkla Warranty Tracker** as part of Tonkla Suite: plugin folder/file is now `tonkla-warranty-tracker/tonkla-warranty-tracker.php`, text domain `tonkla-warranty-tracker`, version 2.0.0.
- Added a guard that prevents running alongside the legacy `it-warranty-tracker` copy.

### Kept (stable API — unchanged)
- Namespace `ITWT\`, `itwt_` prefix, `itwt_*` tables/options/capabilities, `[itwt_warranty_lookup]` shortcode, `itwt/v1` REST routes, existing LINE webhook and printed QR codes.

## [1.5.22] - 2026-10-21

### Changed
- Tonkla Suite branding: developer credit (ต้นกล้า ไอที — https://tonkla.co), unified UTM parameters, emerald admin accent, "Tonkla Warranty" menu with Tonkla mark; aligned header version with `ITWT_VERSION`.
