<?php
namespace Tonkla\ImageOptimizer\Admin;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Ajax_Handlers singleton. AJAX actions keep io_* names in Phase 2 and gain tonkla_img_* aliases in the bridge. */
final class Ajax_Handlers {
	public static function get_instance() {
		return class_exists( 'IO_Ajax_Handlers' ) ? \IO_Ajax_Handlers::get_instance() : null;
	}
}
