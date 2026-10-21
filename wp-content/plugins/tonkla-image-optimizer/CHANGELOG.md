# Changelog — Tonkla Image Optimizer

All notable changes to this plugin are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/), versions follow Semantic Versioning.

## [2.0.3] - 2026-10-21

### Changed
- Admin menu title shortened from "Tonkla - Images Optimizer" to **"TK IMG Optimizer"** so it fits on one line in the admin menu; the page title remains "Tonkla Image Optimizer".

## [2.0.2] - 2026-10-21

### Changed
- Admin menu renamed from "Tonkla Images" to **"Tonkla - Images Optimizer"** (wraps to two lines in the 190px Tonkla admin menu instead of overflowing).
- Menu icon reverted to the original **Dashicons** (`dashicons-images-alt2`) for consistency with the WordPress admin; the experimental favicon-based icon (`tonkla-mark.png`) was removed.

## [2.0.1] - 2026-10-21

### Changed
- Admin menu icon ("Tonkla Images") now uses the Tonkla favicon as a 20×20 grayscale + inverted mark, matching the WordPress admin sidebar and the site-icon treatment in the admin bar — easier to see on the dark menu and sized exactly to WordPress menu-icon dimensions.

## [2.0.0] - 2026-10-21

### Changed
- Renamed to **Tonkla Image Optimizer** as part of Tonkla Suite: plugin folder/file is now `tonkla-image-optimizer/tonkla-image-optimizer.php`, text domain `tonkla-image-optimizer`, version 2.0.0.
- Settings migrate automatically from `io_image_*` to `tonkla_img_*` (legacy keys kept for rollback); WebP meta is written to `_tonkla_img_webp_file` with `_webp_file` kept as a legacy alias; `tonkla_img_*` AJAX actions and filters were added alongside the existing `io_*` ones.
- Added `Tonkla\ImageOptimizer\` PSR-4 classes in `src/` and `TONKLA_IMG_*` constants; legacy `IO_*` API remains available.
- Added a guard that prevents running alongside the legacy `image-optimization` copy.

## [1.0.10] - 2026-10-21

### Changed
- Tonkla Suite branding: developer credit (ต้นกล้า ไอที — https://tonkla.co), unified UTM parameters, emerald admin accent, "Tonkla Images" menu with Tonkla mark, GPL-2.0-or-later licensing metadata.
