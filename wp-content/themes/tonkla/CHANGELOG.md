# Changelog — Tonkla Theme

All notable changes to this theme are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/), versions follow Semantic Versioning.

## [2.0.5] - 2026-10-21

### Changed
- Developer credit can no longer be switched off: the "แสดงเครดิตผู้พัฒนาธีม" checkbox in Tonkla - Theme > ตั้งค่าเว็บไซต์ > ส่วนท้าย is replaced by a read-only notice (a disabled Carbon checkbox would be saved back as false), and `footer.php` now always renders the "พัฒนาโดย ต้นกล้า ไอที" credit. Rationale: the theme is free under the condition that the developer credit stays visible. The `tonkla_show_footer_credit` filter remains as a code-level escape hatch for licensed builds only. The stale `footer_show_credit` option restored from backup is now ignored.

## [2.0.4] - 2026-10-21

### Added
- License card on the "Tonkla - Theme" dashboard (หลังบ้าน): license (GNU GPL v2 or later, linked to gnu.org), copyright holder (ต้นกล้า ไอที), developer site (tonkla.co), third-party components summary (Font Awesome / Alpine.js / Tailwind CSS — MIT/OFL per THIRD-PARTY-NOTICES.md) and a pointer to the full license text in the theme's `LICENSE` file.

### Fixed
- `LICENSE` file header named the wrong product ("Image Optimization — WordPress plugin" left over from a copy); it now correctly reads "Tonkla — WordPress theme" and refers to redistributing "this theme".

## [2.0.3] - 2026-10-21

### Fixed
- Release packaging: `tonkla.zip` no longer embeds the Composer `vendor/` directory (it is git-ignored and caused copy/permission failures when updating the theme over an existing install on some hosts). Run `composer install` in the theme folder to restore the enshrined/svg-sanitize library; without it the theme falls back to its built-in coarse SVG stripping in `tonkla_sanitize_svg_string()`.

## [2.0.2] - 2026-10-21

### Changed
- Admin menu renamed from "TONKLA - IT" to **"Tonkla - Theme"**, aligned with the Tonkla Suite menu naming ("Tonkla - Images Optimizer", "Tonkla - Warranty Lookup"); the dashboard theme label was updated to match.

## [2.0.1] - 2026-10-21

### Changed
- Suite release alignment with Tonkla Image Optimizer 2.0.1 and Tonkla Warranty Tracker 2.0.1 (admin menu icons revised); no theme functionality changes.

## [2.0.0] - 2026-10-21

### Added
- `Tonkla\Theme\` PSR-4 autoloading (`src/`, starting with `Tonkla\Theme\Suite`) for new code; legacy `tonkla_*` functions and Carbon Fields keys are unchanged.
- `LICENSE` (GPL-2.0-or-later) and `THIRD-PARTY-NOTICES.md` for public distribution as part of Tonkla Suite.

### Changed
- Theme metadata aligned to Tonkla Suite: author ต้นกล้า ไอที (https://tonkla.co), GPL-2.0-or-later, WordPress 6.2+ / PHP 8.1+.
- Default admin menu order now includes Tonkla Warranty (`itwt-dashboard`) followed by Tonkla Images (`tonkla-image-optimizer`).

## [1.0.1] - 2026-10-21

### Changed
- Tonkla Suite branding and licensing metadata in `style.css` and README.
