<?php
namespace Tonkla\ImageOptimizer\Admin;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Admin singleton. */
final class Admin {
	public static function get_instance() {
		return class_exists( 'IO_Admin' ) ? \IO_Admin::get_instance() : null;
	}
}
