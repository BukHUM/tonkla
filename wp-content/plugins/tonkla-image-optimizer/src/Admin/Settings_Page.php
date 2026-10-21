<?php
namespace Tonkla\ImageOptimizer\Admin;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Settings_Page singleton. */
final class Settings_Page {
	public static function get_instance() {
		return class_exists( 'IO_Settings_Page' ) ? \IO_Settings_Page::get_instance() : null;
	}
}
