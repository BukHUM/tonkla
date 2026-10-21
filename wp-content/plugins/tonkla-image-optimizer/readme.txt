=== Tonkla Image Optimizer ===
Contributors: tonkla
Tags: images, optimization, webp, resize, cleanup
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Optimize images automatically with resize, WebP conversion, and cleanup unused images.

== Description ==

Image Optimization is a comprehensive WordPress plugin that helps you optimize images automatically to improve website performance and save storage space.

== Features ==

* **Auto Resize**: Automatically resize images on upload to reduce file size
* **WebP Conversion**: Convert images to WebP format for better performance
* **EXIF Stripping**: Remove EXIF data to reduce file size and protect privacy
* **Image Cleanup**: Scan and delete unused thumbnails, WebP files, and orphaned images
* **Statistics Dashboard**: View optimization statistics and results

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/tonkla-image-optimizer/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tonkla Images to configure

== Frequently Asked Questions ==

= Does this plugin work with all themes? =

Yes, this plugin is theme-independent and works with any WordPress theme.

= Will this affect my existing images? =

The plugin only processes new images uploaded after activation. Existing images are not modified unless you use the "Regenerate Images" feature.

= Can I disable WebP conversion? =

Yes, you can disable WebP conversion in the plugin settings.

= What happens to deleted images? =

Deleted images are permanently removed from your server. Make sure to review the scan results before deleting.

== Changelog ==

= 2.0.3 =
* Admin menu title shortened to "TK IMG Optimizer" (page title remains Tonkla Image Optimizer).

= 2.0.2 =
* Admin menu renamed to "Tonkla - Images Optimizer" and menu icon reverted to the original Dashicons (dashicons-images-alt2) for consistency with the WordPress admin.

= 2.0.1 =
* Admin menu icon revised to a 20x20 grayscale + inverted Tonkla favicon mark.

= 2.0.0 =
* Renamed to Tonkla Image Optimizer (Tonkla Suite): new folder/text domain tonkla-image-optimizer, settings migrated to tonkla_img_* with legacy io_image_* kept, Tonkla\ImageOptimizer PSR-4 classes added, guard against running alongside the legacy copy.

= 1.0.10 =
* Tonkla Suite branding, unified credit/UTM, emerald admin accent.

= 1.0.0 =
* Initial release
* Auto resize on upload
* WebP conversion
* EXIF stripping
* Image cleanup functionality
* Admin interface

== Upgrade Notice ==

= 1.0.0 =
Initial release of Image Optimization plugin.

