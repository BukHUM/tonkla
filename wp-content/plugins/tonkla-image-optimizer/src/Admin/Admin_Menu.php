<?php
namespace Tonkla\ImageOptimizer\Admin;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Admin_Menu singleton. */
final class Admin_Menu {
	public static function get_instance() {
		return class_exists( 'IO_Admin_Menu' ) ? \IO_Admin_Menu::get_instance() : null;
	}
}
